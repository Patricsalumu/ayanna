<?php
namespace App\Http\Controllers;

use App\Models\Historiquepdv;
use App\Models\PointDeVente;
use App\Models\Entreprise;
use App\Models\Produit;
use App\Models\StockJournalier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PermissionService;
use App\Services\ModePaiementService;
use Barryvdh\DomPDF\Facade\Pdf; // tout en haut

class StockJournalierController extends Controller
{
    public function __construct(protected PermissionService $permissionService)
    {
    }

    // Affiche la fiche de stock journalier pour une session donnée
    public function index(Request $request, $pointDeVenteId = null)
    {
        $session = $request->get('session');
        $sessionFrom = $request->get('session_from', $session);
        $sessionTo = $request->get('session_to', $session);
        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id');
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = auth()->user()->point_de_vente_id ?? null;
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = PointDeVente::first()?->id;
        }

        if (!$pointDeVenteId) {
            return view('stock_journalier.index', [
                'stocks' => collect(),
                'date' => now()->toDateString(),
                'produits' => collect(),
                'pointDeVenteId' => null,
                'message' => 'Aucun point de vente disponible.'
            ]);
        }

        $selectedCategoryIds = $request->exists('categories')
            ? array_values(array_filter(array_map('intval', (array) $request->input('categories', []))))
            : null;

        $data = $this->getStockJournalierSessionData($pointDeVenteId, $session, $selectedCategoryIds, $sessionFrom, $sessionTo);
        return view('stock_journalier.index', $data);
    }

    private function getStockJournalierSessionData($pointDeVenteId, $session = null, ?array $selectedCategoryIds = null, $sessionFrom = null, $sessionTo = null)
    {
        $pointDeVente = PointDeVente::find($pointDeVenteId);
        $nomPointDeVente = $pointDeVente ? $pointDeVente->nom : null;
        $categories = $pointDeVente ? $pointDeVente->categories()->orderBy('nom')->get() : collect();
        $sessions = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('session')
            ->pluck('session')
            ->unique()
            ->values();

        if (!$session && $sessions->count() > 0) {
            $session = $sessions->first();
        }

        $sessionFrom = $sessionFrom ?: $session;
        $sessionTo = $sessionTo ?: $sessionFrom;
        $fromIndex = $sessions->search($sessionFrom);
        $toIndex = $sessions->search($sessionTo);
        if ($fromIndex === false) $fromIndex = $toIndex !== false ? $toIndex : 0;
        if ($toIndex === false) $toIndex = $fromIndex;
        $rangeStart = min($fromIndex, $toIndex);
        $rangeEnd = max($fromIndex, $toIndex);
        $selectedSessions = $sessions->slice($rangeStart, $rangeEnd - $rangeStart + 1)->values();
        $sessionFrom = $selectedSessions->last() ?? $sessionFrom;
        $sessionTo = $selectedSessions->first() ?? $sessionTo;
        $session = $sessionTo;

        $stocks = StockJournalier::with('produit')
            ->where('point_de_vente_id', $pointDeVenteId)
            ->when($selectedSessions->isNotEmpty(), function ($q) use ($selectedSessions) {
                $q->whereIn('session', $selectedSessions->all());
            })
            ->get();

        $produitsQuery = $pointDeVente ? $pointDeVente->produits()->with('categorie')->orderBy('nom') : null;
        if ($selectedCategoryIds !== null) {
            if (empty($selectedCategoryIds)) {
                $produits = collect();
            } else {
                $produits = $produitsQuery?->whereIn('categorie_id', $selectedCategoryIds)->get() ?? collect();
            }
        } else {
            $produits = $produitsQuery?->get() ?? collect();
        }

        $date = $stocks->first()?->date ?? now()->toDateString();
        $sessionLabel = null;
        if ($sessionFrom && $sessionTo) {
            $formatSession = function ($value) {
                return strlen($value) === 14 && ctype_digit($value)
                    ? Carbon::createFromFormat('YmdHis', $value)->format('d/m/Y H:i:s')
                    : $value;
            };
            $sessionLabel = $sessionFrom === $sessionTo
                ? $formatSession($sessionFrom)
                : $formatSession($sessionFrom).' au '.$formatSession($sessionTo);
        } elseif ($session) {
            if (strlen($session) === 14 && ctype_digit($session)) {
                $sessionLabel = Carbon::createFromFormat('YmdHis', $session)->format('d/m/Y H:i:s');
            } else {
                $sessionLabel = $session;
            }
        }

        $heureOuverture = null;
        $heureFermeture = null;
        $sessionEnCours = false;
        $firstStock = null;
        if ($sessionFrom) {
            $firstStock = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->where('session', $sessionFrom)
                ->orderBy('created_at')
                ->first();
            if ($firstStock && $firstStock->validated_at) {
                $heureOuverture = Carbon::parse($firstStock->validated_at);
            }
            $fermeture = Historiquepdv::where('point_de_vente_id', $pointDeVenteId)
                ->where('etat', 'ferme')
                ->where('opened_at', $firstStock?->validated_at)
                ->first();
            if ($sessionTo !== $sessionFrom) {
                $lastStock = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
                    ->where('session', $sessionTo)
                    ->orderByDesc('created_at')
                    ->first();
                $fermeture = Historiquepdv::where('point_de_vente_id', $pointDeVenteId)
                    ->where('etat', 'ferme')
                    ->where('opened_at', $lastStock?->validated_at)
                    ->first();
            }
            if ($fermeture && $fermeture->closed_at) {
                $heureFermeture = Carbon::parse($fermeture->closed_at);
            } else {
                $sessionEnCours = true;
            }
        }

        $ventesParProduit = [];
        if ($stocks->isNotEmpty() && $heureOuverture) {
            $ventesParProduit = DB::table('panier_produit')
                ->join('paniers', 'paniers.id', '=', 'panier_produit.panier_id')
                ->where('paniers.point_de_vente_id', $pointDeVenteId)
                ->where('paniers.status', '!=', 'annulé')
                ->when($heureOuverture, function ($q) use ($heureOuverture) {
                    return $q->where('paniers.created_at', '>=', $heureOuverture);
                })
                ->when($heureFermeture, function ($q) use ($heureFermeture) {
                    return $q->where('paniers.created_at', '<=', $heureFermeture);
                })
                ->groupBy('panier_produit.produit_id')
                ->selectRaw('panier_produit.produit_id, SUM(panier_produit.quantite) as qty')
                ->pluck('qty', 'produit_id')
                ->toArray();
        }

        $produitsData = $produits->map(function ($produit) use ($stocks, $ventesParProduit, $sessionFrom) {
            $stocksProduit = $stocks->where('produit_id', $produit->id);
            $stock = $stocksProduit->sortByDesc('session')->first();
            $stockInitial = $stocksProduit->where('session', $sessionFrom)->first();
            $q_init = $stockInitial->quantite_initiale ?? $stock->quantite_initiale ?? 0;
            $q_ajout = $stocksProduit->sum('quantite_ajoutee');
            $q_vendue = $ventesParProduit[$produit->id] ?? ($stock->quantite_vendue ?? 0);
            $q_total = $q_init + $q_ajout;
            $q_reste = $q_total - $q_vendue;
            $prix = $produit->prix_vente;
            $total = $q_vendue * $prix;
            $prixAchat = (float) ($produit->prix_achat ?? 0);
            $cout = $q_vendue * $prixAchat;
            $marge = $total - $cout;

            return [
                'produit_id' => $produit->id,
                'stock_id' => $stock?->id,
                'categorie_id' => $produit->categorie_id,
                'categorie' => $produit->categorie?->nom ?? 'Sans catégorie',
                'nom' => $produit->nom,
                'q_init' => $q_init,
                'q_ajout' => $q_ajout,
                'q_total' => $q_total,
                'q_vendue' => $q_vendue,
                'q_reste' => $q_reste,
                'prix' => $prix,
                'prix_achat' => $prixAchat,
                'total' => $total,
                'cout' => $cout,
                'marge' => $marge,
            ];
        });

        $produitsByCategory = $produitsData
            ->sortBy(fn ($produit) => $produit['nom'])
            ->groupBy('categorie')
            ->map(function ($produits) {
                return $produits->sortBy('nom')->values();
            });

        $categoryTotals = $produitsByCategory->map(function ($produits) {
            return $produits->sum('total');
        });
        $categoryCosts = $produitsByCategory->map(function ($produits) {
            return $produits->sum('cout');
        });
        $categoryMargins = $produitsByCategory->map(function ($produits) {
            return $produits->sum('marge');
        });

        $totalVente = $categoryTotals->sum();
        $totalCout = $categoryCosts->sum();
        $totalMarge = $categoryMargins->sum();

        $sessionStart = $heureOuverture ?? Carbon::parse($date)->startOfDay();
        $sessionEnd = $heureFermeture ?? Carbon::parse($date)->endOfDay();

        // Règle métier demandée:
        // - Remises = somme de total_remise des paniers de la session concernée
        // - Créances = paniers de la session dont le mode = compte_client
        // Une session peut être tracée par date panier OU date commande selon les flux.
        $paniersSession = \App\Models\Panier::with(['produits', 'commande'])
            ->where('point_de_vente_id', $pointDeVenteId)
            ->where(function ($q) use ($sessionStart, $sessionEnd) {
                $q->whereBetween('paniers.created_at', [$sessionStart, $sessionEnd])
                    ->orWhereBetween('paniers.updated_at', [$sessionStart, $sessionEnd])
                    ->orWhereHas('commande', function ($cq) use ($sessionStart, $sessionEnd) {
                        $cq->whereBetween('created_at', [$sessionStart, $sessionEnd]);
                    });
            })
            ->whereIn('status', ['valide', 'validé'])
            ->get();

        // Fallback: si la fenêtre session stricte ne retourne rien,
        // recalculer sur la date de session pour éviter les faux zéros.
        if ($paniersSession->isEmpty()) {
            $paniersSession = \App\Models\Panier::with(['produits', 'commande'])
                ->where('point_de_vente_id', $pointDeVenteId)
                ->where(function ($q) use ($date) {
                    $q->whereDate('paniers.created_at', $date)
                        ->orWhereDate('paniers.updated_at', $date)
                        ->orWhereHas('commande', function ($cq) use ($date) {
                            $cq->whereDate('created_at', $date);
                        });
                })
                ->whereIn('status', ['valide', 'validé'])
                ->get();
        }

        $totalRemise = $paniersSession->sum(function ($panier) {
            return (float) ($panier->total_remise ?? $panier->remise ?? 0);
        });

        $totalCreance = $paniersSession
            ->filter(function ($panier) {
                $rawMode = (string) ($panier->commande?->mode_paiement ?? $panier->mode_paiement ?? '');
                $mode = strtolower(str_replace([' ', '-'], '_', $rawMode));
                return $mode === 'compte_client';
            })
            ->sum(function ($panier) {
                $montant = (float) ($panier->total_ttc ?? $panier->total ?? 0);

                if ($montant <= 0 && $panier->relationLoaded('produits')) {
                    $brut = $panier->produits->sum(function ($produit) {
                        return ($produit->pivot->quantite ?? 0) * (($produit->pivot->prix ?? $produit->prix_vente) ?? 0);
                    });
                    $remise = (float) ($panier->total_remise ?? $panier->remise ?? 0);
                    $montant = max(0, $brut - $remise);
                }

                return $montant;
            });

        $totalOffre = $paniersSession
            ->filter(function ($panier) {
                $rawMode = (string) ($panier->commande?->mode_paiement ?? $panier->mode_paiement ?? '');
                $mode = strtolower(str_replace([' ', '-'], '_', $rawMode));
                return $mode === 'offre';
            })
            ->sum(function ($panier) {
                $montant = (float) ($panier->total_ttc ?? $panier->total ?? 0);

                if ($montant <= 0 && $panier->relationLoaded('produits')) {
                    $brut = $panier->produits->sum(function ($produit) {
                        return ($produit->pivot->quantite ?? 0) * (($produit->pivot->prix ?? $produit->prix_vente) ?? 0);
                    });
                    $remise = (float) ($panier->total_remise ?? $panier->remise ?? 0);
                    $montant = max(0, $brut - $remise);
                }

                return $montant;
            });

        $modesPaiement = app(ModePaiementService::class)->actifs($pointDeVente?->entreprise ?? Entreprise::first());
        $totauxParModePaiement = $modesPaiement->mapWithKeys(fn ($mode) => [$mode->nom => 0.0]);

        foreach ($paniersSession as $panier) {
            $rawMode = strtolower((string) ($panier->commande?->mode_paiement ?? $panier->mode_paiement ?? ''));
            $modeNorm = str_replace([' ', '-', 'é', 'è', 'ê', 'à'], ['_', '_', 'e', 'e', 'e', 'a'], $rawMode);

            $modeCode = match ($modeNorm) {
                'especes', 'espece', 'cash' => 'especes',
                'mobile_money', 'mobilemoney' => 'mobile_money',
                'carte', 'card' => 'carte',
                'offre' => 'offre',
                'compte_client', 'credit', 'compteclient' => 'compte_client',
                default => $modeNorm,
            };
            $configuredMode = $modesPaiement->first(fn ($mode) => $mode->code === $modeCode || strtolower($mode->nom) === strtolower($rawMode));
            $label = $configuredMode?->nom;

            if (!$label) {
                continue;
            }

            $montant = (float) ($panier->total_ttc ?? $panier->total ?? 0);
            if ($montant <= 0 && $panier->relationLoaded('produits')) {
                $brut = $panier->produits->sum(function ($produit) {
                    return ($produit->pivot->quantite ?? 0) * (($produit->pivot->prix ?? $produit->prix_vente) ?? 0);
                });
                $remise = (float) ($panier->total_remise ?? $panier->remise ?? 0);
                $montant = max(0, $brut - $remise);
            }

            $totauxParModePaiement[$label] = ((float) $totauxParModePaiement[$label]) + $montant;
        }

        $soldeNet = $totalVente - $totalRemise - $totalCreance - $totalOffre;

        $entreprise = $pointDeVente?->entreprise ?? Entreprise::first();

        return compact(
            'pointDeVente',
            'nomPointDeVente',
            'entreprise',
            'pointDeVenteId',
            'categories',
            'sessions',
            'stocks',
            'produits',
            'produitsByCategory',
            'categoryTotals',
            'categoryCosts',
            'categoryMargins',
            'date',
            'session',
            'sessionFrom',
            'sessionTo',
            'sessionLabel',
            'heureOuverture',
            'heureFermeture',
            'sessionEnCours',
            'ventesParProduit',
            'totalVente',
            'totalCout',
            'totalMarge',
            'totalRemise',
            'totalCreance',
            'totalOffre',
            'soldeNet',
            'totauxParModePaiement'
        );
    }

    // Saisie ou modification de la quantité ajoutée du stock journalier pour un produit
    public function storeqtajoute(Request $request)
    {
        $data = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'date' => 'required|date',
            'session' => 'nullable|string',
            'quantite_ajoutee' => 'required|integer',
            'point_de_vente_id' => 'required|exists:points_de_vente,id',
        ]);

        // On récupère la ligne de stock correspondant à la session sélectionnée, ou la dernière session du jour si elle n'est pas précisée
        $stockQuery = StockJournalier::where('produit_id', $data['produit_id'])
            ->where('date', $data['date'])
            ->where('point_de_vente_id', $data['point_de_vente_id']);
        if (!empty($data['session'])) {
            $stockQuery->where('session', $data['session']);
        }
        $stock = $stockQuery->orderBy('session', 'desc')->first();

        $quantite_ajoutee = $data['quantite_ajoutee'];
        if ($stock) {
            // Nouvelle logique : on additionne à l'ancienne valeur
            $stock->quantite_ajoutee = ($stock->quantite_ajoutee ?? 0) + $quantite_ajoutee;
            $stock->quantite_reste = ($stock->quantite_reste ?? 0) + $quantite_ajoutee;
            $stock->save();
        }
        return redirect()->back()->with('success', 'Quantité ajoutée enregistrée.');
    }

    // Saisie ou modification de la quantité initiale du stock journalier pour un produit
    public function storeqtinitial(Request $request)
    {
        $data = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'date' => 'required|date',
            'quantite_initiale' => 'required|integer',
            'point_de_vente_id' => 'required|exists:points_de_vente,id',
        ]);

        $stockQuery = StockJournalier::where('produit_id', $data['produit_id'])
            ->where('date', $data['date'])
            ->where('point_de_vente_id', $data['point_de_vente_id']);
        if (!empty($data['session'])) {
            $stockQuery->where('session', $data['session']);
        }
        $stock = $stockQuery->first();

        $quantite_ajoutee = $stock->quantite_ajoutee ?? 0;
        $quantite_vendue = $stock->quantite_vendue ?? 0;
        $quantite_initiale = $data['quantite_initiale'];
        $q_total = $quantite_initiale + $quantite_ajoutee;
        $quantite_reste = $stock ? ($stock->quantite_reste ?? ($q_total - $quantite_vendue)) : ($q_total - $quantite_vendue);

        $saveData = [
            'quantite_initiale' => $quantite_initiale,
            'quantite_ajoutee' => $quantite_ajoutee,
            'quantite_vendue' => $quantite_vendue,
            'quantite_reste' => $quantite_reste,
        ];

        $attributes = [
            'produit_id' => $data['produit_id'],
            'date' => $data['date'],
            'point_de_vente_id' => $data['point_de_vente_id'],
        ];
        if (!empty($data['session'])) {
            $attributes['session'] = $data['session'];
        }

        StockJournalier::updateOrCreate(
            $attributes,
            $saveData
        );
        return redirect()->back()->with('success', 'Quantité initiale enregistrée.');
    }

    public static function filterProduitsForExport($produitsByCategory, bool $onlySold = false)
    {
        if (!$onlySold) {
            return $produitsByCategory;
        }

        return $produitsByCategory
            ->map(function ($produits) {
                return $produits
                    ->filter(fn ($produit) => (int) ($produit['q_vendue'] ?? 0) > 0)
                    ->values();
            })
            ->filter(fn ($produits) => $produits->isNotEmpty());
    }

    public function exportPdf(Request $request, $pointDeVenteId)
    {
        $date = $request->get('date', now()->toDateString());
        $session = $request->get('session');
        $sessionFrom = $request->get('session_from', $session);
        $sessionTo = $request->get('session_to', $session);
        $onlySold = $request->boolean('only_sold');
        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id');
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = auth()->user()->point_de_vente_id ?? null;
        }
        if (!$pointDeVenteId) {
            // Si aucun point de vente n'est fourni, prendre le premier point de vente existant
            $pointDeVenteId = \App\Models\PointDeVente::first()?->id;
        }
        if (!$pointDeVenteId) {
            // Aucun point de vente trouvé, retourner une vue vide ou un message
            return view('stock_journalier.index', [
                'stocks' => collect(),
                'date' => $date,
                'produits' => collect(),
                'pointDeVenteId' => null,
                'message' => 'Aucun point de vente disponible.'
            ]);
        }
        $selectedCategoryIds = $request->exists('categories')
            ? array_values(array_filter(array_map('intval', (array) $request->input('categories', []))))
            : null;

        $data = $this->getStockJournalierSessionData($pointDeVenteId, $session, $selectedCategoryIds, $sessionFrom, $sessionTo);
        $data['produitsByCategory'] = $data['produitsByCategory']->map(function ($produits) {
            return $produits->map(function ($produit) {
                $q_total = ($produit['q_init'] ?? 0) + ($produit['q_ajout'] ?? 0);
                $produit['q_reste'] = $q_total - ($produit['q_vendue'] ?? 0);
                return $produit;
            })->values();
        });

        if ($onlySold) {
            $data['produitsByCategory'] = self::filterProduitsForExport($data['produitsByCategory'], true);
            $data['categoryTotals'] = $data['produitsByCategory']->map(function ($produits) {
                return $produits->sum('total');
            });
            $data['categoryCosts'] = $data['produitsByCategory']->map(function ($produits) {
                return $produits->sum('cout');
            });
            $data['categoryMargins'] = $data['produitsByCategory']->map(function ($produits) {
                return $produits->sum('marge');
            });
            $data['totalVente'] = $data['categoryTotals']->sum();
            $data['totalCout'] = $data['categoryCosts']->sum();
            $data['totalMarge'] = $data['categoryMargins']->sum();
        }

        $fileName = 'stock_journalier_'.$data['date'];
        if ($data['sessionFrom'] ?? null) {
            if (($data['sessionFrom'] ?? '') === ($data['sessionTo'] ?? '')) {
                $sessionFormatted = strlen($data['sessionFrom']) === 14 && ctype_digit($data['sessionFrom'])
                    ? Carbon::createFromFormat('YmdHis', $data['sessionFrom'])->format('Y-m-d_H-i-s')
                    : $this->sanitizeFileName($data['sessionFrom']);
                $fileName .= '_session_'.$sessionFormatted;
            } else {
                $fileName .= '_sessions_'.$this->sanitizeFileName($data['sessionFrom']).'_au_'.$this->sanitizeFileName($data['sessionTo']);
            }
        }
        $fileName .= '.pdf';

        $pdf = Pdf::loadView('stock_journalier.pdf', $data)
            ->setPaper('a4', 'portrait');

        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->set_option('enable_unicode', true);

        return $pdf->download($fileName);
    }

    public function exportPdf80mm(Request $request, $pointDeVenteId)
    {
        $session = $request->get('session');
        $sessionFrom = $request->get('session_from', $session);
        $sessionTo = $request->get('session_to', $session);
        $onlySold = $request->boolean('only_sold');
        $selectedCategoryIds = $request->exists('categories')
            ? array_values(array_filter(array_map('intval', (array) $request->input('categories', []))))
            : null;

        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id') ?? auth()->user()->point_de_vente_id ?? \App\Models\PointDeVente::first()?->id;
        }

        $data = $this->getStockJournalierSessionData($pointDeVenteId, $session, $selectedCategoryIds, $sessionFrom, $sessionTo);

        $produitsByCategory = $data['produitsByCategory'];
        $categoryTotals = $data['categoryTotals'] ?? collect();
        $totalVente = $data['totalVente'] ?? 0;

        if ($onlySold) {
            $produitsByCategory = self::filterProduitsForExport($produitsByCategory, true);
            $categoryTotals = $produitsByCategory->map(function ($produits) {
                return $produits->sum('total');
            });
            $totalVente = $categoryTotals->sum();
        }

        $data['produitsByCategory'] = $produitsByCategory;
        $data['categoryTotals'] = $categoryTotals;
        $data['totalVente'] = $totalVente;

        $exportData = [
            'produitsByCategory' => $produitsByCategory,
            'categoryTotals' => $categoryTotals,
            'totalVente' => $totalVente,
        ] + $data;

        // Filename and session formatting (JJ-MM HH-MM)
        $fileName = 'fiche_stock_80mm_'.$data['date'];
        if ($data['sessionFrom'] ?? null) {
            if (($data['sessionFrom'] ?? '') === ($data['sessionTo'] ?? '')) {
                $sessionFormatted = strlen($data['sessionFrom']) === 14 && ctype_digit($data['sessionFrom'])
                    ? Carbon::createFromFormat('YmdHis', $data['sessionFrom'])->format('d-m H-i')
                    : $this->sanitizeFileName($data['sessionFrom']);
                $fileName .= '_session_'.$sessionFormatted;
            } else {
                $fileName .= '_sessions_'.$this->sanitizeFileName($data['sessionFrom']).'_au_'.$this->sanitizeFileName($data['sessionTo']);
            }
        }
        $fileName .= '.pdf';

        $pdf = Pdf::loadView('stock_journalier.pdf_80mm', $exportData)
            ->setPaper([0, 0, 226.77, 2000], 'portrait');

        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->set_option('enable_unicode', true);

        return $pdf->download($fileName);
    }

    public function exportOpeningPdf(Request $request, $pointDeVenteId)
    {
        $session = $request->get('session');
        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id');
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = auth()->user()->point_de_vente_id ?? null;
        }
        if (!$pointDeVenteId) {
            $pointDeVenteId = PointDeVente::first()?->id;
        }

        if (!$pointDeVenteId) {
            return redirect()->back()->with('message', 'Aucun point de vente disponible pour l’export inventaire.');
        }

        $selectedCategoryIds = $request->exists('categories')
            ? array_values(array_filter(array_map('intval', (array) $request->input('categories', []))))
            : null;

        $data = $this->getStockJournalierOpeningData($pointDeVenteId, $session, $selectedCategoryIds);

        $fileName = 'inventaire_ouverture_'.$data['date'];
        if ($data['session']) {
            $sessionPart = $data['sessionLabel'] ?? $data['session'];
            $fileName .= '_session_'.$this->sanitizeFileName($sessionPart);
        }
        $fileName .= '.pdf';

        return Pdf::loadView('stock_journalier.opening_inventaire_pdf', $data)->download($fileName);
    }

    public function exportOpeningPdf80mm(Request $request, $pointDeVenteId)
    {
        $session = $request->get('session');
        if (!$pointDeVenteId) {
            $pointDeVenteId = $request->get('point_de_vente_id') ?? auth()->user()->point_de_vente_id ?? PointDeVente::first()?->id;
        }

        if (!$pointDeVenteId) {
            return redirect()->back()->with('message', 'Aucun point de vente disponible pour l’export inventaire 80 mm.');
        }

        $selectedCategoryIds = $request->exists('categories')
            ? array_values(array_filter(array_map('intval', (array) $request->input('categories', []))))
            : null;
        $data = $this->getStockJournalierOpeningData($pointDeVenteId, $session, $selectedCategoryIds);

        $fileName = 'inventaire_ouverture_80mm_'.$data['date'];
        if ($data['session']) {
            $sessionPart = $data['sessionLabel'] ?? $data['session'];
            $fileName .= '_session_'.$this->sanitizeFileName($sessionPart);
        }
        $fileName .= '.pdf';

        $pdf = Pdf::loadView('stock_journalier.opening_inventaire_80mm', $data)
            ->setPaper([0, 0, 226.77, 2000], 'portrait');
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->set_option('enable_unicode', true);

        return $pdf->download($fileName);
    }

    private function getStockJournalierOpeningData($pointDeVenteId, $session = null, ?array $selectedCategoryIds = null)
    {
        $pointDeVente = PointDeVente::find($pointDeVenteId);
        $nomPointDeVente = $pointDeVente ? $pointDeVente->nom : null;
        $categories = $pointDeVente ? $pointDeVente->categories()->orderBy('nom')->get() : collect();
        $sessions = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('session')
            ->pluck('session')
            ->unique()
            ->values();

        if (!$session && $sessions->count() > 0) {
            $session = $sessions->first();
        }

        $sessionLabel = null;
        if ($session) {
            if (strlen($session) === 14 && ctype_digit($session)) {
                $sessionLabel = Carbon::createFromFormat('YmdHis', $session)->format('d/m/Y H:i:s');
            } else {
                $sessionLabel = $session;
            }
        }

        $currentStocks = StockJournalier::with('produit')
            ->where('point_de_vente_id', $pointDeVenteId)
            ->when($session, fn($q) => $q->where('session', $session))
            ->get();

        $previousSession = null;
        if ($session) {
            $index = $sessions->search($session);
            if ($index !== false && isset($sessions[$index + 1])) {
                $previousSession = $sessions[$index + 1];
            }
        } elseif ($sessions->count() > 1) {
            $previousSession = $sessions->get(1);
        }

        $previousStocks = collect();
        if ($previousSession) {
            $previousStocks = StockJournalier::with('produit')
                ->where('point_de_vente_id', $pointDeVenteId)
                ->where('session', $previousSession)
                ->get();
        }

        $produitsQuery = $pointDeVente ? $pointDeVente->produits()->with('categorie')->orderBy('nom') : null;
        if ($selectedCategoryIds !== null) {
            if (empty($selectedCategoryIds)) {
                $produits = collect();
            } else {
                $produits = $produitsQuery?->whereIn('categorie_id', $selectedCategoryIds)->get() ?? collect();
            }
        } else {
            $produits = $produitsQuery?->get() ?? collect();
        }
        $date = $currentStocks->first()?->date ?? now()->toDateString();

        $produitsData = $produits->map(function ($produit) use ($currentStocks, $previousStocks) {
            $current = $currentStocks->where('produit_id', $produit->id)->last();
            $previous = $previousStocks->where('produit_id', $produit->id)->last();
            $q_system = $previous->quantite_reste ?? 0;
            $q_counted = $current->quantite_initiale ?? 0;
            $difference = $q_counted - $q_system;

            return [
                'categorie' => $produit->categorie?->nom ?? 'Sans catégorie',
                'nom' => $produit->nom,
                'q_system' => $q_system,
                'q_counted' => $q_counted,
                'difference' => $difference,
            ];
        });

        $produitsByCategory = $produitsData
            ->sortBy(fn ($item) => $item['nom'])
            ->groupBy('categorie')
            ->map(fn ($items) => $items->sortBy('nom')->values());

        $totalDifference = $produitsData->sum('difference');

        return compact(
            'pointDeVente',
            'nomPointDeVente',
            'categories',
            'sessions',
            'date',
            'session',
            'sessionLabel',
            'produitsByCategory',
            'totalDifference'
        );
    }

    private function sanitizeFileName(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $string);
    }

    /**
     * Affiche la fiche d'ouverture du stock journalier pour un point de vente (logique session)
     */
    public function ficheOuvertureStock(Request $request, $pointDeVenteId)
    {
        if (!$this->permissionService->canManageSalesSession(Auth::user())) {
            abort(403, 'Seul un administrateur ou un caissier peut ouvrir une session.');
        }

        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);
        $produits = $pointDeVente->produits()->orderBy('nom')->get();
        $stocksDerniereSession = collect();
        // Trouver la dernière session (toutes dates confondues) pour ce point de vente
        $lastStock = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->first();
        $lastDate = $lastStock ? $lastStock->date : null;
        $lastSession = $lastStock ? $lastStock->session : null;
        // Récupérer les stocks de la dernière session
        $stocks = collect();
        if ($lastDate && $lastSession) {
            $stocks = \App\Models\StockJournalier::where('point_de_vente_id', $pointDeVenteId)
                ->where('date', $lastDate)
                ->where('session', $lastSession)
                ->get();
        }
        // Associer la quantité restée à chaque produit
        foreach ($produits as $produit) {
            $stock = $stocks->where('produit_id', $produit->id)->first();
            $stocksDerniereSession[$produit->id] = $stock ? $stock->quantite_reste : 0;
        }
        $date = now()->toDateString();
        $verrouille = false; // On ne verrouille que si la session est validée (géré à la validation)
        return view('stock_journalier.ouverture', compact('produits', 'pointDeVente', 'date', 'stocksDerniereSession', 'verrouille', 'lastSession'));
    }

    /**
     * Validation de la fiche d'ouverture du stock journalier : enregistre toutes les quantités initiales,
     * marque la fiche comme validée, ouvre le point de vente, puis redirige vers le plan de salle.
     */
    public function validerOuvertureStock(Request $request)
    {
        if (!$this->permissionService->canManageSalesSession(Auth::user())) {
            abort(403, 'Seul un administrateur ou un caissier peut ouvrir une session.');
        }

        $request->validate([
            'date' => 'required|date',
            'point_de_vente_id' => 'required|exists:points_de_vente,id',
            'quantite_initiale' => 'required|array',
        ]);
        $date = $request->date;
        $pointDeVenteId = $request->point_de_vente_id;
        $quantites = $request->quantite_initiale;
        $now = now();
        $session = $now->format('YmdHis'); // session unique
        // Pour chaque produit, on crée une NOUVELLE ligne stock_journalier (même s'il y en a déjà une pour aujourd'hui)
        foreach ($quantites as $produitId => $qte) {
            $stock = new \App\Models\StockJournalier();
            $stock->produit_id = $produitId;
            $stock->date = $date;
            $stock->point_de_vente_id = $pointDeVenteId;
            $stock->quantite_initiale = $qte;
            $stock->quantite_reste = $qte; // Correction : initialisation à la quantité initiale
            $stock->validated_at = $now;
            $stock->session = $session;
            $stock->save();
        }
        // Ouvre le point de vente (état = ouvert) ET historise l'ouverture
        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);
        if ($pointDeVente->etat !== 'ouvert') {
            $pointDeVente->etat = 'ouvert';
            $pointDeVente->save();
            \App\Models\Historiquepdv::create([
                'point_de_vente_id' => $pointDeVente->id,
                'user_id' => Auth::id(),
                'etat' => 'ouvert',
            ]);
        }
        // Redirige vers le plan de salle
        $salle = $pointDeVente->salles()->first();
        if ($salle) {
            return redirect()->route('salle.plan.vente', [
                'entreprise' => $pointDeVente->entreprise_id,
                'salle' => $salle->id,
                'point_de_vente_id' => $pointDeVente->id
            ])->with('success', 'Fiche d\'ouverture validée. Le point de vente est ouvert.');
        }
        return redirect()->route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id])
            ->with('success', 'Fiche d\'ouverture validée.');
    }

    /**
     * Ferme la session courante du point de vente :
     * - Enregistre la date/heure de fermeture et l'utilisateur
     * - Calcule la quantité restée pour chaque produit
     * - Historise la fermeture
     * - Met à jour le point de vente (état = fermé)
     */
    public function fermerSession(Request $request, $pointDeVenteId)
    {
        Log::info('[Fermeture Session] Requête reçue', [
            'point_de_vente_id' => $pointDeVenteId,
            'user_id' => Auth::id(),
            'route' => $request->route()?->getName(),
        ]);

        if (!$this->permissionService->canManageSalesSession(Auth::user())) {
            Log::warning('[Fermeture Session] Permission refusée', [
                'point_de_vente_id' => $pointDeVenteId,
                'user_id' => Auth::id(),
                'role' => Auth::user()?->role,
            ]);
            abort(403, 'Seul un administrateur ou un caissier peut fermer une session.');
        }

        $now = now();
        $userId = Auth::id();
        $date = $now->toDateString();
        $heure = $now->format('H:i:s');
        $pointDeVente = \App\Models\PointDeVente::findOrFail($pointDeVenteId);

        // Vérification backend : empêcher la fermeture si un panier en cours existe
        $paniersEnCours = \App\Models\Panier::where('point_de_vente_id', $pointDeVenteId)
            ->where('status', 'en_cours')
            ->get(['id', 'table_id', 'status']);
        if ($paniersEnCours->isNotEmpty()) {
            Log::warning('[Fermeture Session] Bloquée par des paniers en cours', [
                'point_de_vente_id' => $pointDeVenteId,
                'paniers' => $paniersEnCours->toArray(),
            ]);
            return redirect()->back()->with('error', 'Impossible de fermer : il reste des paniers en cours pour ce point de vente.');
        }

        // Récupérer la dernière session ouverte pour ce point de vente (toutes dates confondues)
        $lastStock = StockJournalier::where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->first();
        $lastDate = $lastStock ? $lastStock->date : null;
        $lastSession = $lastStock ? $lastStock->session : null;
        // DEBUG : log avant test session
        Log::info('[Fermeture Session] Recherche session', [
            'point_de_vente_id' => $pointDeVenteId,
            'lastDate' => $lastDate,
            'lastSession' => $lastSession
        ]);
        if (!$lastSession) {
            Log::warning('[Fermeture Session] Aucune session stock trouvée', [
                'point_de_vente_id' => $pointDeVenteId,
                'dernier_stock_id' => $lastStock?->id,
            ]);
            return redirect()->back()->with('error', 'Aucune session à fermer.');
        }
        // Log pour debug : afficher la dernière date et session trouvées
        Log::info('[DEBUG FERMETURE] lastDate/lastSession', [
            'point_de_vente_id' => $pointDeVenteId,
            'lastDate' => $lastDate,
            'lastSession' => $lastSession
        ]);
        Log::info('[Fermeture Session] Début fermeture', [
            'point_de_vente_id' => $pointDeVenteId,
            'date_session' => (string) $lastDate,
            'session' => (string) $lastSession,
            'validated_at' => $lastStock->validated_at,
            'comptabilite_active' => (bool) $pointDeVente->comptabilite_active,
        ]);
        $stocks = collect();
        $journauxBrouillon = [];
        try {
            DB::transaction(function () use ($pointDeVente, $pointDeVenteId, $lastDate, $lastSession, $lastStock, $now, $userId, &$stocks, &$journauxBrouillon) {
                $stocks = StockJournalier::with('produit')
                    ->where('point_de_vente_id', $pointDeVenteId)
                    ->where('date', $lastDate)
                    ->where('session', $lastSession)
                    ->lockForUpdate()
                    ->get();
                Log::info('[Fermeture Session] Stocks chargés', [
                    'point_de_vente_id' => $pointDeVenteId,
                    'date_session' => (string) $lastDate,
                    'session' => (string) $lastSession,
                    'nombre_lignes_stock' => $stocks->count(),
                    'quantite_vendue' => $stocks->sum('quantite_vendue'),
                ]);

                foreach ($stocks as $stock) {
                    $quantiteTotale = ($stock->quantite_initiale ?? 0) + ($stock->quantite_ajoutee ?? 0);
                    $stock->quantite_reste = $quantiteTotale - ($stock->quantite_vendue ?? 0);
                    $stock->save();
                }

                $solde = $stocks->sum(function ($stock) {
                    return ($stock->quantite_vendue ?? 0) * ($stock->produit?->prix_vente ?? 0);
                });

                Historiquepdv::create([
                    'point_de_vente_id' => $pointDeVente->id,
                    'user_id' => $userId,
                    'etat' => 'ferme',
                    'solde' => $solde,
                    'opened_at' => $lastStock->validated_at ?? null,
                    'closed_at' => $now,
                    'opened_by' => $lastStock->validated_by ?? null,
                    'closed_by' => $userId,
                    'created_at' => $now,
                ]);

                $debutSession = $lastStock->validated_at
                    ? Carbon::parse($lastStock->validated_at)
                    : Carbon::parse($lastDate)->startOfDay();
                $journauxBrouillon = $pointDeVente->comptabilite_active
                    ? app(\App\Services\ComptabiliteService::class)->genererBrouillonsFermetureSession(
                        $pointDeVente,
                        (string) $lastDate,
                        (string) $lastSession,
                        $debutSession,
                        Carbon::parse($now),
                        $userId
                    )
                    : [];

                Log::info('[Fermeture Session] Brouillons comptables préparés', [
                    'point_de_vente_id' => $pointDeVenteId,
                    'session' => (string) $lastSession,
                    'journaux' => collect($journauxBrouillon)->map(fn ($journal) => [
                        'id' => $journal->id,
                        'type' => $journal->type_operation,
                        'numero_piece' => $journal->numero_piece,
                        'montant_total' => $journal->montant_total,
                        'statut' => $journal->statut,
                    ])->values()->all(),
                ]);

                $pointDeVente->etat = 'ferme';
                $pointDeVente->save();
            });
        } catch (\Throwable $e) {
            Log::error('[Fermeture Session] Échec génération brouillons comptables', [
                'point_de_vente_id' => $pointDeVenteId,
                'session' => $lastSession,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'La session n’a pas été fermée : ' . $e->getMessage());
        }

        $this->invalidateOtherSessions();
        // DEBUG : log des infos session fermée
        Log::info('[Fermeture Session] Dernière session trouvée', [
            'point_de_vente_id' => $pointDeVenteId,
            'lastDate' => $lastDate,
            'lastSession' => $lastSession,
            'nbStocks' => $stocks->count(),
            'produits' => $stocks->pluck('produit_id')->toArray(),
        ]);
        Log::info('[Fermeture Session] Fermeture terminée', [
            'point_de_vente_id' => $pointDeVenteId,
            'date_session' => (string) $lastDate,
            'session' => (string) $lastSession,
            'journaux_brouillon' => count($journauxBrouillon),
        ]);
        return redirect()->route('pointsDeVente.show', [$pointDeVente->entreprise_id, $pointDeVente->id])
            ->with('success', 'Session fermée. ' . count($journauxBrouillon) . ' journal(aux) comptable(s) créé(s) en brouillon.');
    }

    private function invalidateOtherSessions(): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('id', '!=', request()->session()->getId())
                ->delete();
        }
    }
}

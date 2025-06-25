<div style="width:100%; font-family: Arial, sans-serif;">
    <div style="text-align:center; font-size:12px; color:#888; margin-bottom:4px;">
        Généré par Ayanna le {{ date('d/m/Y H:i') }}
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <div style="text-align:left;">
            @if(isset($entreprise) && $entreprise->logo)
                <img src="{{ public_path('storage/'.$entreprise->logo) }}" alt="Logo" style="height:60px; margin-bottom:4px;"><br>
            @endif
            @if(isset($entreprise))
                <span style="font-weight:bold; font-size:15px;">{{ $entreprise->nom }}</span><br>
                <span style="font-size:12px;">{{ $entreprise->adresse ?? '' }}</span><br>
                <span style="font-size:12px;">{{ $entreprise->telephone ?? '' }}</span>
            @endif
        </div>
        <div style="text-align:right;">
            @if(isset($pointDeVente))
                <span style="font-weight:bold; font-size:14px; color:#2563eb;">Point de vente : {{ $pointDeVente->nom }}</span><br>
            @endif
        </div>
    </div>
    <h1 style="font-size:22px; color:#2563eb; margin-bottom:12px; text-align:center;">RAPPORT JOURNALIER DU {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h1>
    <div style="max-width: 900px; margin: 0 auto; font-family: 'DejaVu Sans', Arial, sans-serif;">
        <div style="background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px #e5e7eb; padding: 2rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 1rem;">
                <tbody>
                    {{-- RECAP GENERAL --}}
                    <tr>
                        <td style="padding: 12px 16px; font-weight: 600; border: 1px solid #d1d5db;">Recette journalière</td>
                        <td colspan="2" style="padding: 12px 16px; text-align: right; font-weight: bold; color: #059669; border: 1px solid #d1d5db;">{{ number_format($recette, 0, ',', ' ') }} F</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: 600; border: 1px solid #d1d5db;">Total Créances</td>
                        <td colspan="2" style="padding: 12px 16px; text-align: right; font-weight: bold; color: #ea580c; border: 1px solid #d1d5db;">-{{ number_format($totalCreance, 0, ',', ' ') }} F</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: 600; border: 1px solid #d1d5db;">Total Dépenses</td>
                        <td colspan="2" style="padding: 12px 16px; text-align: right; font-weight: bold; color: #b91c1c; border: 1px solid #d1d5db;">-{{ number_format($depenses, 0, ',', ' ') }} F</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 12px 16px; font-weight: bold; color: #1e3a8a; border: 1px solid #d1d5db; background: #e0e7ff;">Solde</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: bold; color: #1e3a8a; font-size: 1.2em; border: 1px solid #d1d5db; background: #e0e7ff;">{{ number_format($solde, 0, ',', ' ') }} F</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 10px 16px; font-weight: bold; color: #c2410c; border: 1px solid #d1d5db; background: #fef3c7;">Détail des Créances</td>
                    </tr>
                    <tr style="background: #f1f5f9;">
                        <td style="padding: 8px 16px; font-weight: 600; border: 1px solid #d1d5db;">Client</td>
                        <td style="padding: 8px 16px; font-weight: 600; border: 1px solid #d1d5db; text-align: center;">Serveuses</td>
                        <td style="padding: 8px 16px; font-weight: 600; border: 1px solid #d1d5db; text-align: right;">Montant</td>
                    </tr>
                    @forelse($detailsCreance as $detail)
                        <tr>
                            <td style="padding: 8px 16px; border: 1px solid #d1d5db;">{{ $detail['client'] }}</td>
                            <td style="padding: 8px 16px; border: 1px solid #d1d5db; text-align: center;">
                                @foreach($detail['serveuses'] as $serv)
                                    <span style="display: inline-block; background: #dbeafe; color: #1e40af; border-radius: 8px; padding: 2px 8px; font-size: 0.95em; margin: 2px;">{{ $serv }}</span>
                                @endforeach
                            </td>
                            <td style="padding: 8px 16px; border: 1px solid #d1d5db; text-align: right; color: #ea580c; font-weight: 600;">{{ number_format($detail['total'], 0, ',', ' ') }} F</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="padding: 8px 16px; color: #9ca3af; font-style: italic; text-align: right; border: 1px solid #d1d5db;">Aucune créance ce jour</td></tr>
                    @endforelse
                    <tr>
                        <td colspan="3" style="padding: 10px 16px; font-weight: bold; color: #b91c1c; border: 1px solid #d1d5db; background: #fee2e2;">Détail des Dépenses</td>
                    </tr>
                    <tr style="background: #f1f5f9;">
                        <td style="padding: 8px 16px; font-weight: 600; border: 1px solid #d1d5db;">Compte</td>
                        <td style="padding: 8px 16px; font-weight: 600; border: 1px solid #d1d5db;">Libellé</td>
                        <td style="padding: 8px 16px; font-weight: 600; border: 1px solid #d1d5db; text-align: right;">Montant</td>
                    </tr>
                    @php
                        $depensesList = \App\Models\EntreeSortie::whereDate('created_at', $date)
                            ->where('point_de_vente_id', request()->route('pointDeVenteId'))
                            ->whereHas('compte', function($q) {
                                $q->where('type', 'passif');
                            })->get();
                    @endphp
                    @forelse($depensesList as $dep)
                        <tr>
                            <td style="padding: 8px 16px; border: 1px solid #d1d5db;"><span style="font-weight: 600;">{{ $dep->compte->nom ?? '' }}</span></td>
                            <td style="padding: 8px 16px; border: 1px solid #d1d5db;"><span style="color: #6b7280; font-size: 0.97em;">{{ $dep->libele ?? $dep->motif ?? '' }}</span></td>
                            <td style="padding: 8px 16px; border: 1px solid #d1d5db; text-align: right; color: #b91c1c; font-weight: 600;">-{{ number_format($dep->montant, 0, ',', ' ') }} F</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="padding: 8px 16px; color: #9ca3af; font-style: italic; text-align: right; border: 1px solid #d1d5db;">Aucune dépense ce jour</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

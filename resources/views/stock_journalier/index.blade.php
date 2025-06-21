@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-8 bg-white rounded-xl shadow-lg p-6">
    <h1 class="text-2xl font-bold text-blue-700 mb-4">Fiche de stock journalier du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h1>
    @if(isset($nomPointDeVente))
        <div class="mb-4 text-blue-700 bg-blue-50 rounded px-4 py-2 font-semibold">
            Point de vente courant : {{ $nomPointDeVente }}
        </div>
    @endif
    <form method="GET" class="mb-6 flex gap-2 items-end">
        <label class="font-semibold">Date :</label>
        <input type="date" name="date" value="{{ $date }}" class="border rounded px-3 py-2" onchange="this.form.submit()">
        @if(isset($sessions) && $sessions->count() > 1)
            <label class="font-semibold ml-4">Session :</label>
            <select name="session" class="border rounded px-3 py-2" onchange="this.form.submit()">
                @foreach($sessions as $sess)
                    @if($sess && strlen($sess) === 14 && ctype_digit($sess))
                        <option value="{{ $sess }}" @if($sess == $session) selected @endif>
                            {{ \Carbon\Carbon::createFromFormat('YmdHis', $sess)->format('d/m/Y H:i:s') }}
                        </option>
                    @else
                        <option value="{{ $sess }}" @if($sess == $session) selected @endif>
                            Session inconnue ({{ $sess }})
                        </option>
                    @endif
                @endforeach
            </select>
        @endif
    </form>
    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-100 rounded px-4 py-2">{{ session('success') }}</div>
    @endif
    @if(isset($message))
        <div class="mb-4 text-red-700 bg-red-100 rounded px-4 py-2">{{ $message }}</div>
    @endif

    <div class="flex justify-end mb-4">
        <a href="{{ route('stock_journalier.export_pdf', ['pointDeVente' => $pointDeVenteId, 'date' => $date]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-semibold rounded hover:bg-red-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Exporter en PDF
        </a>
    </div>
    <div class="overflow-x-auto">
    <table class="min-w-full bg-white border rounded-lg text-sm">
        <thead>
            <tr class="bg-blue-100 text-blue-700">
                <th class="px-2 py-2">#ID Stock</th>
                <th class="px-2 py-2">Produit</th>
                <th class="px-2 py-2">Q. Initiale</th>
                <th class="px-2 py-2">Q. Ajoutée</th>
                <th class="px-2 py-2">Q. Totale</th>
                <th class="px-2 py-2">Q. Vendue</th>
                <th class="px-2 py-2">Q. Restée</th>
                <th class="px-2 py-2">Prix unitaire</th>
                <th class="px-2 py-2">Total</th>
                <th class="px-2 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($produits as $produit)
            @php
                // Correction : prendre la dernière ligne de stock du jour pour ce produit
                $stock = $stocks->where('produit_id', $produit->id)->last();
                $q_init = $stock->quantite_initiale ?? 0;
                $q_ajout = $stock->quantite_ajoutee ?? 0;
                $q_vendue = $stock->quantite_vendue ?? 0;
                $q_total = $q_init + $q_ajout;
                $q_reste = $stock->quantite_reste ?? ($q_total - $q_vendue);
                $prix = $produit->prix_vente;
                $total = $q_vendue * $prix;
            @endphp
            <tr class="border-b">
                <td class="px-2 py-2 text-xs text-gray-500">{{ $stock->id ?? '-' }}</td>
                <td class="px-2 py-2 font-semibold">{{ $produit->nom }}</td>
                <td class="px-2 py-2">{{ $q_init }}</td>
                <td class="px-2 py-2">{{ $q_ajout }}</td>
                <td class="px-2 py-2 text-center">{{ $q_total }}</td>
                <td class="px-2 py-2">{{ $q_vendue }}</td>
                <td class="px-2 py-2">{{ $q_reste }}</td>
                <td class="px-2 py-2 text-right">{{ number_format($prix, 0, ',', ' ') }} F</td>
                <td class="px-2 py-2 text-right font-bold">{{ number_format($total, 0, ',', ' ') }} F</td>
                <td class="px-2 py-2 text-center">
                    <form method="POST" action="{{ url('stock-journalier/qtajoute') }}" class="inline">
                        @csrf
                        <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="point_de_vente_id" value="{{ $pointDeVenteId }}">
                        <input type="number" name="quantite_ajoutee" value="{{ $q_ajout }}" min="0" class="border rounded px-2 py-1 w-20 mr-2" style="width:70px;">
                        <button type="submit" class="text-blue-600 hover:text-blue-900" title="Ajouter / Modifier">
                            <svg xmlns='http://www.w3.org/2000/svg' class='inline h-6 w-6' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/>
                            </svg>
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4 text-right text-lg font-bold text-blue-700">
        @php
            $totalVente = 0;
            foreach($produits as $produit) {
                $stock = $stocks->where('produit_id', $produit->id)->last();
                $q_vendue = $stock->quantite_vendue ?? 0;
                $prix = $produit->prix_vente;
                $totalVente += $q_vendue * $prix;
            }
        @endphp
        Total vente session : {{ number_format($totalVente, 0, ',', ' ') }} F
    </div>
    </div>

    <!-- Modal -->
    <div id="modal-stock" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
            <h2 id="modal-title" class="text-xl font-bold mb-4 text-blue-700">Ajout Stock</h2>
            <form id="modal-form" method="POST" action="{{ url('stock-journalier') }}">
                @csrf
                <input type="hidden" name="produit_id" id="modal-produit-id">
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="point_de_vente_id" value="{{ $pointDeVenteId }}">
                <div class="mb-3">
                    <label class="block font-semibold mb-1">Quantité ajoutée :</label>
                    <input type="number" name="quantite_ajoutee" id="modal-quantite-ajoutee" class="border rounded px-3 py-2 w-full" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded hover:bg-blue-700">Enregistrer</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(produitId) {
            // Chercher le nom du produit dans le tableau
            var row = document.querySelector('button[onclick="openModal(\''+produitId+'\')"]').closest('tr');
            var nomProduit = row.querySelector('td').innerText;
            document.getElementById('modal-title').innerText = 'Ajoutez Produit ' + nomProduit;
            document.getElementById('modal-produit-id').value = produitId;
            document.getElementById('modal-quantite-ajoutee').value = '';
            document.getElementById('modal-stock').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('modal-stock').classList.add('hidden');
        }
    </script>
</div>
@endsection

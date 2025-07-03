@extends('layouts.appsalle')
@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-6">
    <!-- Bouton retour stylisé Ayanna -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('salles.show', $entreprise->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-green-400 to-blue-500 text-white font-semibold shadow hover:from-green-500 hover:to-blue-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span>Retour aux salles</span>
        </a>
    </div>
    <div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">
    <!-- Barre d'outils globale et onglets zones -->
    <div class="flex justify-between items-center mb-2">
        <!-- Onglets zones à droite -->
        <div class="flex gap-2">
            @foreach($entreprise->salles as $zone)
                <a href="{{ route('salle.plan', [$entreprise->id, $zone->id]) }}"
                   class="px-4 py-2 rounded font-semibold shadow
                   {{ $zone->id === $salle->id ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-700 hover:bg-green-100' }}">
                    {{ $zone->nom }}
                </a>
            @endforeach
            <button id="openAddSalleModal" type="button" class="px-4 py-2 rounded bg-gray-200 text-gray-700 font-semibold shadow hover:bg-green-100">+</button>
        </div>
        <!-- Barre d'outils à gauche -->
        <div class="flex gap-2">
            <button id="addTableBtn" class="bg-white border border-gray-400 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded shadow flex items-center gap-1" title="Ajouter une table"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>Table</button>
            <button id="savePlanBtn" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded shadow ml-2" title="Enregistrer le plan">Enregistrer</button>
        </div>
    </div>

    <!-- Zone du plan -->
    <div id="plan" class="relative w-full min-w-[400px] h-[500px] border border-gray-300 rounded bg-gray-100 overflow-x-auto overflow-y-hidden" style="max-width:100vw; background-image: linear-gradient(0deg, transparent 24%, #e5e7eb 25%, #e5e7eb 26%, transparent 27%, transparent 74%, #e5e7eb 75%, #e5e7eb 76%, transparent 77%, transparent), linear-gradient(90deg, transparent 24%, #e5e7eb 25%, #e5e7eb 26%, transparent 27%, transparent 74%, #e5e7eb 75%, #e5e7eb 76%, transparent 77%, transparent); background-size: 40px 40px;">
        <div class="relative w-max h-full">
        @foreach ($salle->tables as $table)
            @php
                $style = 'top:' . $table->position_y . 'px;'
                    . 'left:' . $table->position_x . 'px;'
                    . 'width:' . ($table->width ?? 70) . 'px;'
                    . 'height:' . ($table->height ?? 70) . 'px;'
                    . ($table->forme === 'cercle' ? 'border-radius:50%;' : '')
                    . 'background:' . ($table->is_busy ? '#4ade80' : '#f3f4f6') . ';'
                    . 'border-color:#22c55e;';
            @endphp
            <div class="table-item absolute cursor-pointer border-4 flex items-center justify-center group shadow-lg"
                 data-id="{{ $table->id }}"
                 data-forme="{{ $table->forme }}"
                 data-numero="{{ $table->numero }}"
                 tabindex="0"
                 style="{{ $style }}"
            >
                <span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">{{ $table->numero }}</span>
                @if(isset($table->nb_commandes) && $table->nb_commandes > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5 shadow">{{ $table->nb_commandes }}</span>
                @endif
            </div>
        @endforeach
        <!-- Menu contextuel d'actions pour la table sélectionnée -->
        <div id="tableActionsMenu" class="hidden absolute z-50 bg-white border border-gray-300 rounded shadow-lg p-2 flex gap-2 items-center">
            <input id="editNumeroInput" type="number" class="border rounded px-2 py-1 w-16 text-sm" style="width:60px;" />
            <button id="btn-shape-rect" class="bg-gray-200 rounded px-2 py-1 text-xs border border-gray-400" title="Carré">&#9632;</button>
            <button id="btn-shape-cercle" class="bg-gray-200 rounded-full px-2 py-1 text-xs border border-gray-400" title="Cercle">&#9679;</button>
            <button id="btn-duplicate" class="bg-blue-500 text-white rounded px-2 py-1 text-xs border border-blue-600" title="Dupliquer"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 8h6a2 2 0 002-2V6a2 2 0 00-2-2H9a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></button>
            <button id="btn-delete" class="bg-red-500 text-white rounded px-2 py-1 text-xs border border-red-600" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        </div>
    </div>

    <!-- Formulaire d'ajout de table (AJAX) -->
    <!-- Modale d'ajout de table -->
    <x-modal name="add-table-modal">
        <form id="addTableForm" method="POST" action="{{ route('tables.store') }}" class="w-full max-w-md mx-auto bg-white rounded-lg shadow-lg p-6 flex flex-col gap-4">
            @csrf
            <h2 class="text-xl font-bold text-gray-800 mb-2 text-center">Ajouter une table</h2>
            <input type="hidden" name="salle_id" value="{{ $salle->id }}">
            <div>
                <label class="block text-sm font-semibold mb-1">Numéro</label>
                <input type="number" name="numero" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring focus:border-blue-400" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Forme</label>
                <select name="forme" class="border border-gray-300 rounded px-3 py-2 w-full">
                    <option value="rectangle">Rectangle</option>
                    <option value="cercle">Cercle</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" onclick="window.dispatchEvent(new CustomEvent('close-modal', {detail: 'add-table-modal'}))">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter</button>
            </div>
        </form>
    </x-modal>

    <!-- Modale d'ajout de salle -->
    <x-modal name="add-salle-modal">
        <form id="addSalleForm" method="POST" action="{{ route('salles.store', $entreprise->id) }}" class="w-full max-w-md mx-auto bg-white rounded-lg shadow-lg p-6 flex flex-col gap-4">
            @csrf
            <h2 class="text-xl font-bold text-gray-800 mb-2 text-center">Créer une nouvelle salle</h2>
            <div>
                <label class="block text-sm font-semibold mb-1">Nom de la salle</label>
                <input type="text" name="nom" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring focus:border-green-400" required>
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" onclick="window.dispatchEvent(new CustomEvent('close-modal', {detail: 'add-salle-modal'}))">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Ajouter</button>
            </div>
        </form>
    </x-modal>
</div>

<style>
    .table-item {
        min-width: 50px;
        min-height: 50px;
        resize: both;
        overflow: hidden;
        box-shadow: 0 2px 8px 0 #0001;
        border-color: #22c55e !important;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-radius 0.2s, background 0.2s, box-shadow 0.2s;
        background-clip: padding-box;
        outline: none;
    }
    .table-item.selected {
        box-shadow: 0 0 0 3px #6366f1, 0 2px 8px 0 #0001;
        z-index: 20;
    }
    .table-item .table-num {
        font-size: 1.3rem;
        font-weight: bold;
        margin: auto;
        pointer-events: none;
        color: #222;
    }
    .btn-shape {
        cursor: pointer;
    }
    .table-item[style*='background: #4ade80'] {
        border-color: #22c55e !important;
    }
    // Gestion des boutons de forme
    document.querySelectorAll('.btn-shape').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const shape = this.dataset.shape;
            const tableDiv = this.closest('.table-item');
            if (shape === 'cercle') {
                tableDiv.style.borderRadius = '50%';
            } else {
                tableDiv.style.borderRadius = '0';
            }
            // Envoi AJAX pour sauvegarder la forme
            const id = tableDiv.dataset.id;
            fetch(`/tables/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    forme: shape
                })
            });
            // Met à jour l'attribut data-forme
            tableDiv.dataset.forme = shape;
        });
    });
</style>

<script>

    // Sélection de table et menu contextuel
    let selectedTable = null;
    const menu = document.getElementById('tableActionsMenu');
    const numeroInput = document.getElementById('editNumeroInput');
    let menuTableId = null;

    function deselectTable() {
        if (selectedTable) selectedTable.classList.remove('selected');
        selectedTable = null;
        menu.classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.table-item') && !e.target.closest('#tableActionsMenu')) {
            deselectTable();
        }
    });

    document.querySelectorAll('.table-item').forEach(el => {
        el.addEventListener('mousedown', mouseDownHandler);
        el.addEventListener('click', function(e) {
            e.stopPropagation();
            deselectTable();
            selectedTable = this;
            selectedTable.classList.add('selected');
            // Positionner le menu à côté de la table
            const rect = selectedTable.getBoundingClientRect();
            const planRect = document.getElementById('plan').getBoundingClientRect();
            menu.style.left = (rect.right - planRect.left + 10) + 'px';
            menu.style.top = (rect.top - planRect.top) + 'px';
            menu.classList.remove('hidden');
            // Préremplir le numéro
            numeroInput.value = selectedTable.dataset.numero;
            menuTableId = selectedTable.dataset.id;
        });
    });

    // Ouvre la modale d'ajout de salle
    document.getElementById('openAddSalleModal').addEventListener('click', function() {
        window.dispatchEvent(new CustomEvent('open-modal', {detail: 'add-salle-modal'}));
    });

    // Ouvre la modale d'ajout de table
    document.getElementById('addTableBtn').addEventListener('click', function() {
        // Réinitialise le formulaire à chaque ouverture
        const form = document.getElementById('addTableForm');
        form.reset();
        // Réactive le bouton si besoin
        form.querySelector('button[type="submit"]').disabled = false;
        window.dispatchEvent(new CustomEvent('open-modal', {detail: 'add-table-modal'}));
    });


    // Soumission AJAX du formulaire d'ajout de salle (corrigé)
    (function() {
        const form = document.getElementById('addSalleForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (submitBtn.disabled) return;
            submitBtn.disabled = true;
            const data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
            .then(res => res.json())
            .then(salle => {
                if (!salle.id) throw new Error('Erreur');
                window.dispatchEvent(new CustomEvent('close-modal', {detail: 'add-salle-modal'}));
                // Recharge la page pour afficher la nouvelle salle (ou redirige)
                window.location.href = `/entreprises/{{ $entreprise->id }}/salles/${salle.id}/plan`;
            })
            .catch(() => alert('Erreur lors de la création de la salle.'))
            .finally(() => {
                submitBtn.disabled = false;
            });
        });
    })();

    // Gestion unique de la soumission du formulaire d'ajout de table
    (function() {
        const form = document.getElementById('addTableForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        // On retire tout handler existant (si jamais)
        form.addEventListener('submit', handleAddTableSubmit);
        function handleAddTableSubmit(e) {
            e.preventDefault();
            if (submitBtn.disabled) return;
            submitBtn.disabled = true;
            const data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
            .then(res => res.json())
            .then(table => {
                // Ajoute la table sur le plan
                const plan = document.getElementById('plan');
                const div = document.createElement('div');
                div.className = 'table-item absolute cursor-move border-4 flex items-center justify-center group shadow-lg';
                div.dataset.id = table.id;
                div.dataset.forme = table.forme;
                div.tabIndex = 0;
                div.setAttribute('contenteditable', 'false');
                div.style.top = (table.position_y ?? 20) + 'px';
                div.style.left = (table.position_x ?? 20) + 'px';
                div.style.width = (table.width ?? 70) + 'px';
                div.style.height = (table.height ?? 70) + 'px';
                if (table.forme === 'cercle') div.style.borderRadius = '50%';
                div.style.background = '#f3f4f6';
                div.style.borderColor = '#22c55e';
                div.innerHTML = `<span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">${table.numero}</span>`;
                div.addEventListener('mousedown', mouseDownHandler);
                // Ajout du handler d'édition (click)
                div.addEventListener('click', function(e) {
                    e.stopPropagation();
                    deselectTable();
                    selectedTable = div;
                    selectedTable.classList.add('selected');
                    const rect = selectedTable.getBoundingClientRect();
                    const planRect = document.getElementById('plan').getBoundingClientRect();
                    menu.style.left = (rect.right - planRect.left + 10) + 'px';
                    menu.style.top = (rect.top - planRect.top) + 'px';
                    menu.classList.remove('hidden');
                    numeroInput.value = selectedTable.dataset.numero;
                    menuTableId = selectedTable.dataset.id;
                });
                plan.appendChild(div);
                form.reset();
                window.dispatchEvent(new CustomEvent('close-modal', {detail: 'add-table-modal'}));
            })
            .catch(() => alert('Erreur lors de l’ajout de la table.'))
            .finally(() => {
                submitBtn.disabled = false;
            });
        }
    })();

    // Changement de forme
    document.getElementById('btn-shape-rect').addEventListener('click', function() {
        if (!menuTableId) return;
        fetch(`/tables/${menuTableId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                _token: '{{ csrf_token() }}',
                forme: 'rectangle'
            })
        }).then(() => {
            if (selectedTable) {
                selectedTable.style.borderRadius = '10px';
                selectedTable.dataset.forme = 'rectangle';
            }
        });
    });
    document.getElementById('btn-shape-cercle').addEventListener('click', function() {
        if (!menuTableId) return;
        fetch(`/tables/${menuTableId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                _token: '{{ csrf_token() }}',
                forme: 'cercle'
            })
        }).then(() => {
            if (selectedTable) {
                selectedTable.style.borderRadius = '50%';
                selectedTable.dataset.forme = 'cercle';
            }
        });
    });

    // Suppression
    document.getElementById('btn-delete').addEventListener('click', function() {
        if (!menuTableId) return;
        if (!confirm('Supprimer cette table ?')) return;
        fetch(`/tables/${menuTableId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        }).then(() => {
            if (selectedTable) selectedTable.remove();
            deselectTable();
        });
    });

    // Duplication
    document.getElementById('btn-duplicate').addEventListener('click', function() {
        if (!menuTableId) return;
        // Récupérer width/height réels (pour duplication fidèle)
        let width = selectedTable.style.width ? parseInt(selectedTable.style.width) : 70;
        let height = selectedTable.style.height ? parseInt(selectedTable.style.height) : 70;
        // Calculer le nouveau numéro (numéro + 1)
        let numero = parseInt(selectedTable.dataset.numero);
        if (isNaN(numero)) numero = 1;
        let nouveauNumero = numero + 1;
        fetch(`/tables`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: new URLSearchParams({
                salle_id: '{{ $salle->id }}',
                numero: nouveauNumero,
                forme: selectedTable.dataset.forme,
                position_x: (parseInt(selectedTable.style.left) + 30),
                position_y: (parseInt(selectedTable.style.top) + 30),
                width: width,
                height: height
            })
        })
        .then(res => res.json())
        .then(table => {
            // Ajoute la table dupliquée sur le plan
            const plan = document.getElementById('plan');
            const div = document.createElement('div');
            div.className = 'table-item absolute cursor-pointer border-4 flex items-center justify-center group shadow-lg';
            div.dataset.id = table.id;
            div.dataset.forme = table.forme;
            div.dataset.numero = table.numero;
            div.tabIndex = 0;
            div.style.top = (table.position_y ?? 20) + 'px';
            div.style.left = (table.position_x ?? 20) + 'px';
            div.style.width = (table.width ?? 70) + 'px';
            div.style.height = (table.height ?? 70) + 'px';
            if (table.forme === 'cercle') div.style.borderRadius = '50%';
            div.style.background = '#f3f4f6';
            div.style.borderColor = '#22c55e';
            div.innerHTML = `<span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">${table.numero}</span>`;
            div.addEventListener('mousedown', mouseDownHandler);
            div.addEventListener('click', function(e) {
                e.stopPropagation();
                deselectTable();
                selectedTable = div;
                selectedTable.classList.add('selected');
                const rect = selectedTable.getBoundingClientRect();
                const planRect = document.getElementById('plan').getBoundingClientRect();
                menu.style.left = (rect.right - planRect.left + 10) + 'px';
                menu.style.top = (rect.top - planRect.top) + 'px';
                menu.classList.remove('hidden');
                numeroInput.value = selectedTable.dataset.numero;
                menuTableId = selectedTable.dataset.id;
            });
            plan.appendChild(div);
            deselectTable();
        });
    });

    let offsetX = 0;
    let offsetY = 0;
    let currentTable = null;

    function mouseDownHandler(e) {
        currentTable = e.target.closest('.table-item');
        offsetX = e.offsetX;
        offsetY = e.offsetY;
        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
    }

    function mouseMoveHandler(e) {
        if (!currentTable) return;
        const plan = document.getElementById('plan');
        const rect = plan.getBoundingClientRect();
        let left = e.clientX - rect.left - offsetX;
        let top = e.clientY - rect.top - offsetY;
        currentTable.style.left = left + 'px';
        currentTable.style.top = top + 'px';
    }

    function mouseUpHandler(e) {
        document.removeEventListener('mousemove', mouseMoveHandler);
        document.removeEventListener('mouseup', mouseUpHandler);
        saveTablePosition(e);
        currentTable = null;
    }


    // Sauvegarde globale du plan (positions de toutes les tables)
    document.getElementById('savePlanBtn').addEventListener('click', function() {
        const tables = document.querySelectorAll('.table-item');
        const updates = [];
        tables.forEach(table => {
            const id = table.dataset.id;
            updates.push(fetch(`/tables/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    position_x: parseInt(table.style.left),
                    position_y: parseInt(table.style.top),
                    width: parseInt(table.style.width),
                    height: parseInt(table.style.height),
                })
            }));
        });
        Promise.all(updates).then(() => {
            this.textContent = '✔ Enregistré';
            this.classList.remove('bg-purple-700');
            this.classList.add('bg-green-600');
            setTimeout(() => {
                this.textContent = 'Enregistrer';
                this.classList.remove('bg-green-600');
                this.classList.add('bg-purple-700');
            }, 1500);
        });
    });


    // Ouvre la modale d'ajout de table (corrigé, ouverture immédiate sans toggle)
    document.getElementById('addTableBtn').addEventListener('click', function() {
        const form = document.getElementById('addTableForm');
        form.reset();
        form.querySelector('button[type="submit"]').disabled = false;
        window.dispatchEvent(new CustomEvent('open-modal', {detail: 'add-table-modal'}));
    });

    // Désactive l'édition inline du numéro (tout passe par le menu)
    function saveTableNumero(e) {}

    // Ajout dynamique de table (anti double soumission)
    (function() {
        const form = document.getElementById('addTableForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        let submitting = false;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (submitting || submitBtn.disabled) return;
            submitting = true;
            submitBtn.disabled = true;
            const data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
            .then(res => res.json())
            .then(table => {
                // Ajoute la table sur le plan
                const plan = document.getElementById('plan');
                const div = document.createElement('div');
                div.className = 'table-item absolute cursor-move border-4 flex items-center justify-center group shadow-lg';
                div.dataset.id = table.id;
                div.dataset.forme = table.forme;
                div.tabIndex = 0;
                div.setAttribute('contenteditable', 'false');
                div.style.top = (table.position_y ?? 20) + 'px';
                div.style.left = (table.position_x ?? 20) + 'px';
                div.style.width = (table.width ?? 70) + 'px';
                div.style.height = (table.height ?? 70) + 'px';
                if (table.forme === 'cercle') div.style.borderRadius = '50%';
                div.style.background = '#f3f4f6';
                div.style.borderColor = '#22c55e';
                div.innerHTML = `<span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">${table.numero}</span>`;
                div.addEventListener('mousedown', mouseDownHandler);
                plan.appendChild(div);
                form.reset();
                window.dispatchEvent(new CustomEvent('close-modal', {detail: 'add-table-modal'}));
            })
            .catch(() => alert('Erreur lors de l’ajout de la table.'))
            .finally(() => {
                submitting = false;
                submitBtn.disabled = false;
            });
        });
    })();

    // Edition du numéro de table (valide sur perte de focus ou touche Entrée)
    numeroInput.addEventListener('blur', saveTableNumero);
    numeroInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveTableNumero();
            numeroInput.blur(); // Pour fermer le menu si besoin
        }
    });
    function saveTableNumero() {
        if (!menuTableId) return;
        const newNumero = numeroInput.value;
        fetch(`/tables/${menuTableId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                _token: '{{ csrf_token() }}',
                numero: newNumero
            })
        })
        .then(res => res.json())
        .then(data => {
            if (selectedTable) {
                selectedTable.dataset.numero = newNumero;
                const numSpan = selectedTable.querySelector('.table-num');
                if(numSpan) numSpan.textContent = newNumero;
            }
        });
    }
</script>
@endsection

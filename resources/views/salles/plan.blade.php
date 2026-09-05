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

    <div id="assignModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">Affecter une serveuse</h3>
            <div>
                <p class="mb-2">Table <span id="assignTableNumero" class="font-bold">-</span></p>
                <select id="assignServeuseSelect" class="w-full border rounded px-3 py-2">
                    <option value="">Aucune serveuse</option>
                    @foreach($entreprise->users()->where('role', 'serveuse')->get() as $serveuse)
                        <option value="{{ $serveuse->id }}">{{ $serveuse->name }}</option>
                    @endforeach
                </select>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" id="closeAssignModalBtn" class="px-4 py-2 rounded bg-gray-200">Annuler</button>
                    <button type="button" id="saveAssignBtn" class="px-4 py-2 rounded bg-blue-600 text-white">Appliquer au brouillon</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone du plan -->
    <div id="plan" class="relative w-full h-[500px] border border-gray-300 rounded bg-gray-100 overflow-hidden" style="background-image: linear-gradient(0deg, transparent 24%, #e5e7eb 25%, #e5e7eb 26%, transparent 27%, transparent 74%, #e5e7eb 75%, #e5e7eb 76%, transparent 77%, transparent), linear-gradient(90deg, transparent 24%, #e5e7eb 25%, #e5e7eb 26%, transparent 27%, transparent 74%, #e5e7eb 75%, #e5e7eb 76%, transparent 77%, transparent); background-size: 40px 40px;">

        @foreach ($salle->tables as $table)
            <button type="button"
                    class="table-item absolute cursor-pointer border-4 flex items-center justify-center group shadow-lg"
                    data-id="{{ $table->id }}"
                    data-forme="{{ $table->forme }}"
                    data-numero="{{ $table->numero }}"
                    data-serveuse-id="{{ $table->serveuse_id ?? '' }}"
                          data-serveuse-name="{{ $table->serveuse?->name ?? '' }}"
                    tabindex="0"
                 style="
                    top: {{ $table->position_y }}px;
                    left: {{ $table->position_x }}px;
                          width: {{ $table->width ?? 80 }}px;
                          height: {{ $table->height ?? 80 }}px;
                    @if ($table->forme === 'cercle') border-radius: 50%; @endif
                    background: {{ $table->is_busy ? '#4ade80' : '#f3f4f6' }};
                    border-color: #22c55e;
                 "
            >
                <span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">{{ $table->numero }}</span>
                @if(isset($table->nb_commandes) && $table->nb_commandes > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5 shadow">{{ $table->nb_commandes }}</span>
                @endif
            </button>
        @endforeach
        <!-- Menu contextuel d'actions pour la table sélectionnée -->
        <div id="tableActionsMenu" class="hidden absolute z-50 bg-white border border-gray-300 rounded shadow-lg p-2 flex gap-2 items-center">
            <input id="editNumeroInput" type="number" class="border rounded px-2 py-1 w-16 text-sm" style="width:60px;" />
            <span id="currentServeuseLabel" class="px-2 py-1 rounded bg-emerald-50 text-emerald-800 text-xs font-semibold max-w-[170px] truncate" title="Serveuse affectée">Serveuse: -</span>
            <button id="btn-assign" class="bg-emerald-500 text-white rounded px-2 py-1 text-xs border border-emerald-600" title="Affecter">Serveuse</button>
            <button id="btn-shape-rect" class="bg-gray-200 rounded px-2 py-1 text-xs border border-gray-400" title="Carré">&#9632;</button>
            <button id="btn-shape-cercle" class="bg-gray-200 rounded-full px-2 py-1 text-xs border border-gray-400" title="Cercle">&#9679;</button>
            <button id="btn-duplicate" class="bg-blue-500 text-white rounded px-2 py-1 text-xs border border-blue-600" title="Dupliquer"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 8h6a2 2 0 002-2V6a2 2 0 00-2-2H9a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></button>
            <button id="btn-delete" class="bg-red-500 text-white rounded px-2 py-1 text-xs border border-red-600" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
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
            <div>
                <label class="block text-sm font-semibold mb-1">Serveuse</label>
                <select name="serveuse_id" class="border border-gray-300 rounded px-3 py-2 w-full">
                    <option value="">Aucune serveuse</option>
                    @foreach($entreprise->users()->where('role', 'serveuse')->get() as $serveuse)
                        <option value="{{ $serveuse->id }}">{{ $serveuse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" onclick="window.dispatchEvent(new CustomEvent('close-modal', {detail: 'add-table-modal'}))">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter au brouillon</button>
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
</style>

@php
    $serveuseNameMap = $entreprise->users()
        ->where('role', 'serveuse')
        ->get()
        ->mapWithKeys(fn($serveuse) => [(string) $serveuse->id => $serveuse->name])
        ->all();
@endphp
<script>
    const serveuseNameById = @json($serveuseNameMap);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    const plan = document.getElementById('plan');
    const menu = document.getElementById('tableActionsMenu');
    const numeroInput = document.getElementById('editNumeroInput');
    const currentServeuseLabel = document.getElementById('currentServeuseLabel');
    const savePlanBtn = document.getElementById('savePlanBtn');
    const addTableBtn = document.getElementById('addTableBtn');
    const addTableForm = document.getElementById('addTableForm');
    const openAddSalleModalBtn = document.getElementById('openAddSalleModal');
    const addSalleForm = document.getElementById('addSalleForm');

    const assignModal = document.getElementById('assignModal');
    const assignTableNumero = document.getElementById('assignTableNumero');
    const assignServeuseSelect = document.getElementById('assignServeuseSelect');
    const closeAssignModalBtn = document.getElementById('closeAssignModalBtn');
    const saveAssignBtn = document.getElementById('saveAssignBtn');

    let selectedTable = null;
    let currentTable = null;
    let offsetX = 0;
    let offsetY = 0;
    let hasUnsavedChanges = false;
    const deletedTableIds = new Set();

    function toNumber(value, fallback = 0) {
        const n = Number(value);
        return Number.isFinite(n) ? n : fallback;
    }

    function isPersistedTable(el) {
        return /^\d+$/.test(String(el?.dataset?.id || ''));
    }

    function markDirty() {
        hasUnsavedChanges = true;
        savePlanBtn.textContent = 'Enregistrer *';
        savePlanBtn.classList.remove('bg-purple-700');
        savePlanBtn.classList.add('bg-amber-600');
    }

    function markSaved() {
        hasUnsavedChanges = false;
        savePlanBtn.textContent = 'Enregistrer';
        savePlanBtn.classList.remove('bg-amber-600');
        savePlanBtn.classList.add('bg-purple-700');
    }

    function showSavedFeedback() {
        savePlanBtn.textContent = '✔ Enregistré';
        savePlanBtn.classList.remove('bg-amber-600', 'bg-purple-700');
        savePlanBtn.classList.add('bg-green-600');
        setTimeout(() => {
            savePlanBtn.classList.remove('bg-green-600');
            savePlanBtn.classList.add('bg-purple-700');
            savePlanBtn.textContent = 'Enregistrer';
        }, 1400);
    }

    function tableNumeroExists(numero, exceptId = null) {
        const normalized = String(numero || '').trim();
        if (!normalized) return false;

        return Array.from(plan.querySelectorAll('.table-item')).some((el) => {
            if (!el.isConnected) return false;
            if (exceptId && String(el.dataset.id) === String(exceptId)) return false;
            return String(el.dataset.numero || '').trim() === normalized;
        });
    }

    function getNextNumero() {
        let maxNumero = 0;
        plan.querySelectorAll('.table-item').forEach((el) => {
            const n = parseInt(el.dataset.numero || '0', 10);
            if (Number.isFinite(n) && n > maxNumero) {
                maxNumero = n;
            }
        });
        return maxNumero + 1;
    }

    function styleTableElement(el) {
        const forme = String(el.dataset.forme || 'rectangle');
        el.style.borderRadius = forme === 'cercle' ? '50%' : '10px';
        el.style.background = '#f3f4f6';
        el.style.borderColor = '#22c55e';
    }

    function createTableElement(data) {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = 'table-item absolute cursor-pointer border-4 flex items-center justify-center group shadow-lg';
        el.dataset.id = String(data.id);
        el.dataset.forme = String(data.forme || 'rectangle');
        el.dataset.numero = String(data.numero || '');
        el.dataset.serveuseId = data.serveuse_id ? String(data.serveuse_id) : '';
        el.dataset.serveuseName = data.serveuse_name ? String(data.serveuse_name) : '';
        if (String(data.id).startsWith('new-')) {
            el.dataset.isNew = '1';
        }

        el.style.top = `${toNumber(data.position_y, 20)}px`;
        el.style.left = `${toNumber(data.position_x, 20)}px`;
        el.style.width = `${toNumber(data.width, 80)}px`;
        el.style.height = `${toNumber(data.height, 80)}px`;
        styleTableElement(el);

        el.innerHTML = `<span class="table-num text-center w-full select-none flex items-center justify-center" style="pointer-events:none; font-size:1.3rem; font-weight:bold; color:#222;">${data.numero || ''}</span>`;
        attachTableHandlers(el);
        return el;
    }

    function positionMenuNear(el) {
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.style.visibility = 'hidden';
        }

        const rect = el.getBoundingClientRect();
        const planRect = plan.getBoundingClientRect();
        const gap = 10;
        const menuWidth = menu.offsetWidth || 260;
        const menuHeight = menu.offsetHeight || 42;

        const tableLeft = rect.left - planRect.left;
        const tableRight = rect.right - planRect.left;

        let left = tableRight + gap;
        if (left + menuWidth > plan.clientWidth) {
            left = Math.max(0, tableLeft - menuWidth - gap);
        }

        let top = rect.top - planRect.top;
        if (top + menuHeight > plan.clientHeight) {
            top = Math.max(0, plan.clientHeight - menuHeight - 6);
        }

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
        menu.style.visibility = 'visible';
    }

    function updateServeuseLabel(el) {
        if (!currentServeuseLabel) return;
        if (!el) {
            currentServeuseLabel.textContent = 'Serveuse: -';
            currentServeuseLabel.title = 'Serveuse non affectée';
            return;
        }

        const name = String(el.dataset.serveuseName || '').trim();
        currentServeuseLabel.textContent = `Serveuse: ${name || '-'}`;
        currentServeuseLabel.title = name ? `Serveuse: ${name}` : 'Serveuse non affectée';
    }

    function selectTable(el) {
        if (!el) return;
        if (selectedTable) selectedTable.classList.remove('selected');
        selectedTable = el;
        selectedTable.classList.add('selected');
        menu.classList.remove('hidden');
        positionMenuNear(selectedTable);
        numeroInput.value = selectedTable.dataset.numero || '';
        updateServeuseLabel(selectedTable);
    }

    function deselectTable() {
        if (selectedTable) selectedTable.classList.remove('selected');
        selectedTable = null;
        menu.classList.add('hidden');
        updateServeuseLabel(null);
    }

    function openAssignModal() {
        if (!selectedTable) return;
        assignTableNumero.textContent = selectedTable.dataset.numero || '-';
        assignServeuseSelect.value = selectedTable.dataset.serveuseId || '';
        assignModal.classList.remove('hidden');
        assignModal.classList.add('flex');
    }

    function closeAssignModal() {
        assignModal.classList.add('hidden');
        assignModal.classList.remove('flex');
    }

    function saveTableNumeroLocal() {
        if (!selectedTable) return;
        const raw = String(numeroInput.value || '').trim();
        const nextNumero = parseInt(raw, 10);

        if (!Number.isFinite(nextNumero) || nextNumero <= 0) {
            numeroInput.value = selectedTable.dataset.numero || '';
            return;
        }

        if (tableNumeroExists(nextNumero, selectedTable.dataset.id)) {
            alert('Ce numéro de table existe déjà dans cette salle.');
            numeroInput.value = selectedTable.dataset.numero || '';
            return;
        }

        selectedTable.dataset.numero = String(nextNumero);
        const numSpan = selectedTable.querySelector('.table-num');
        if (numSpan) numSpan.textContent = String(nextNumero);
        markDirty();
    }

    function mouseDownHandler(e) {
        const target = e.currentTarget;
        currentTable = target.closest('.table-item');
        if (!currentTable) return;

        const rect = currentTable.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
    }

    function mouseMoveHandler(e) {
        if (!currentTable) return;
        const rect = plan.getBoundingClientRect();
        const maxLeft = Math.max(0, rect.width - currentTable.offsetWidth);
        const maxTop = Math.max(0, rect.height - currentTable.offsetHeight);
        const left = Math.max(0, Math.min(e.clientX - rect.left - offsetX, maxLeft));
        const top = Math.max(0, Math.min(e.clientY - rect.top - offsetY, maxTop));

        currentTable.style.left = `${Math.round(left)}px`;
        currentTable.style.top = `${Math.round(top)}px`;
        if (selectedTable === currentTable) {
            positionMenuNear(currentTable);
        }
    }

    function mouseUpHandler() {
        document.removeEventListener('mousemove', mouseMoveHandler);
        document.removeEventListener('mouseup', mouseUpHandler);
        if (currentTable) {
            markDirty();
        }
        currentTable = null;
    }

    function attachTableHandlers(el) {
        el.addEventListener('mousedown', mouseDownHandler);
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            selectTable(el);
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.table-item') && !e.target.closest('#tableActionsMenu') && !e.target.closest('#assignModal')) {
            deselectTable();
        }
    });

    plan.querySelectorAll('.table-item').forEach((el) => attachTableHandlers(el));

    openAddSalleModalBtn.addEventListener('click', function () {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-salle-modal' }));
    });

    addTableBtn.addEventListener('click', function () {
        addTableForm.reset();
        const numeroField = addTableForm.querySelector('input[name="numero"]');
        numeroField.value = String(getNextNumero());
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-table-modal' }));
    });

    addSalleForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = addSalleForm.querySelector('button[type="submit"]');
        if (submitBtn.disabled) return;
        submitBtn.disabled = true;

        fetch(addSalleForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: new FormData(addSalleForm),
        })
            .then((res) => res.json())
            .then((salle) => {
                if (!salle.id) throw new Error('Réponse invalide');
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'add-salle-modal' }));
                window.location.href = `/entreprises/{{ $entreprise->id }}/salles/${salle.id}/plan`;
            })
            .catch(() => alert('Erreur lors de la création de la salle.'))
            .finally(() => {
                submitBtn.disabled = false;
            });
    });

    addTableForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const data = new FormData(addTableForm);
        const numero = parseInt(String(data.get('numero') || ''), 10);
        if (!Number.isFinite(numero) || numero <= 0) {
            alert('Veuillez saisir un numéro de table valide.');
            return;
        }
        if (tableNumeroExists(numero)) {
            alert('Ce numéro de table existe déjà dans cette salle.');
            return;
        }

        const tmpId = `new-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        const tableData = {
            id: tmpId,
            numero,
            forme: String(data.get('forme') || 'rectangle'),
            serveuse_id: data.get('serveuse_id') ? Number(data.get('serveuse_id')) : null,
            serveuse_name: data.get('serveuse_id') ? (serveuseNameById[String(data.get('serveuse_id'))] || '') : '',
            position_x: 20,
            position_y: 20,
            width: 80,
            height: 80,
        };

        const newTableEl = createTableElement(tableData);
        plan.appendChild(newTableEl);
        selectTable(newTableEl);
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'add-table-modal' }));
        markDirty();
    });

    document.getElementById('btn-assign').addEventListener('click', function (e) {
        e.stopPropagation();
        openAssignModal();
    });

    closeAssignModalBtn.addEventListener('click', function () {
        closeAssignModal();
    });

    assignModal.addEventListener('click', function (e) {
        if (e.target === assignModal) {
            closeAssignModal();
        }
    });

    saveAssignBtn.addEventListener('click', function () {
        if (!selectedTable) {
            closeAssignModal();
            return;
        }
        const serveuseId = assignServeuseSelect.value || '';
        selectedTable.dataset.serveuseId = serveuseId;
        selectedTable.dataset.serveuseName = serveuseId ? (serveuseNameById[String(serveuseId)] || '') : '';
        updateServeuseLabel(selectedTable);
        markDirty();
        closeAssignModal();
    });

    document.getElementById('btn-shape-rect').addEventListener('click', function () {
        if (!selectedTable) return;
        selectedTable.dataset.forme = 'rectangle';
        styleTableElement(selectedTable);
        markDirty();
    });

    document.getElementById('btn-shape-cercle').addEventListener('click', function () {
        if (!selectedTable) return;
        selectedTable.dataset.forme = 'cercle';
        styleTableElement(selectedTable);
        markDirty();
    });

    document.getElementById('btn-delete').addEventListener('click', function () {
        if (!selectedTable) return;
        if (!confirm('Supprimer cette table ?')) return;

        if (isPersistedTable(selectedTable)) {
            deletedTableIds.add(String(selectedTable.dataset.id));
        }

        selectedTable.remove();
        deselectTable();
        markDirty();
    });

    document.getElementById('btn-duplicate').addEventListener('click', function () {
        if (!selectedTable) return;

        const width = toNumber(selectedTable.style.width.replace('px', ''), 80);
        const height = toNumber(selectedTable.style.height.replace('px', ''), 80);
        const left = toNumber(selectedTable.style.left.replace('px', ''), 20);
        const top = toNumber(selectedTable.style.top.replace('px', ''), 20);

        const cloneData = {
            id: `new-${Date.now()}-${Math.floor(Math.random() * 1000)}`,
            numero: getNextNumero(),
            forme: selectedTable.dataset.forme || 'rectangle',
            serveuse_id: selectedTable.dataset.serveuseId || null,
            position_x: left + 24,
            position_y: top + 24,
            width,
            height,
        };

        const cloneEl = createTableElement(cloneData);
        plan.appendChild(cloneEl);
        selectTable(cloneEl);
        markDirty();
    });

    numeroInput.addEventListener('blur', saveTableNumeroLocal);
    numeroInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        saveTableNumeroLocal();
    });

    savePlanBtn.addEventListener('click', async function () {
        if (!hasUnsavedChanges && deletedTableIds.size === 0) {
            showSavedFeedback();
            return;
        }

        savePlanBtn.disabled = true;
        savePlanBtn.textContent = 'Enregistrement...';

        const tableElements = Array.from(plan.querySelectorAll('.table-item'));
        const newTables = tableElements.filter((el) => el.dataset.isNew === '1');
        const existingTables = tableElements.filter((el) => el.dataset.isNew !== '1' && isPersistedTable(el));

        try {
            for (const id of deletedTableIds) {
                await fetch(`/tables/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
            }

            for (const el of newTables) {
                const payload = new URLSearchParams({
                    salle_id: '{{ $salle->id }}',
                    numero: String(toNumber(el.dataset.numero, getNextNumero())),
                    forme: String(el.dataset.forme || 'rectangle'),
                    position_x: String(toNumber(el.style.left.replace('px', ''), 20)),
                    position_y: String(toNumber(el.style.top.replace('px', ''), 20)),
                    width: String(toNumber(el.style.width.replace('px', ''), 80)),
                    height: String(toNumber(el.style.height.replace('px', ''), 80)),
                });

                const serveuseId = String(el.dataset.serveuseId || '').trim();
                if (serveuseId !== '') {
                    payload.append('serveuse_id', serveuseId);
                }

                const response = await fetch('/tables', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: payload,
                });

                const body = await response.json();
                const created = body?.table ?? body;
                if (!response.ok || !created?.id) {
                    throw new Error('Erreur lors de la création d\'une table');
                }

                el.dataset.id = String(created.id);
                el.dataset.isNew = '0';
                el.dataset.numero = String(created.numero ?? el.dataset.numero);
                el.dataset.forme = String(created.forme ?? el.dataset.forme);
                el.dataset.serveuseId = created.serveuse_id ? String(created.serveuse_id) : (el.dataset.serveuseId || '');
                el.dataset.serveuseName = el.dataset.serveuseId ? (serveuseNameById[String(el.dataset.serveuseId)] || '') : '';
                const numSpan = el.querySelector('.table-num');
                if (numSpan) {
                    numSpan.textContent = String(el.dataset.numero || '');
                }
                styleTableElement(el);
            }

            for (const el of existingTables) {
                await fetch(`/tables/${el.dataset.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        numero: toNumber(el.dataset.numero, 1),
                        forme: String(el.dataset.forme || 'rectangle'),
                        position_x: toNumber(el.style.left.replace('px', ''), 20),
                        position_y: toNumber(el.style.top.replace('px', ''), 20),
                        width: toNumber(el.style.width.replace('px', ''), 80),
                        height: toNumber(el.style.height.replace('px', ''), 80),
                        serveuse_id: String(el.dataset.serveuseId || '').trim() === '' ? null : Number(el.dataset.serveuseId),
                    }),
                });
            }

            deletedTableIds.clear();
            markSaved();
            showSavedFeedback();
        } catch (error) {
            alert('Erreur lors de l\'enregistrement du plan.');
            markDirty();
        } finally {
            savePlanBtn.disabled = false;
        }
    });

    window.addEventListener('beforeunload', function (e) {
        if (!hasUnsavedChanges && deletedTableIds.size === 0) return;
        e.preventDefault();
        e.returnValue = '';
    });
</script>
@endsection

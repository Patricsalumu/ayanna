<x-app1-layout>
    <div class="py-6">
        @if(isset($noEntrepriseMessage))
            <div class="flex flex-col items-center justify-center space-y-3">
                <h3 class="text-lg font-bold text-[#3e2f24] text-center">
                    {{ $noEntrepriseMessage }}
                </h3>
                <a href="{{ route('entreprises.create') }}" 
                   class="px-4 py-2 bg-[#d8c1a8] text-[#3e2f24] rounded-lg shadow hover:scale-105 transition flex items-center justify-center gap-2 text-sm">
                    <span class="text-lg">➕</span> Créer mon entreprise
                </a>
            </div>
        @elseif(isset($modules) && $modules->count())
            <div class="max-w-4xl mx-auto">
                <h3 class="text-base font-bold text-[#3e2f24] text-center mb-4">Applications disponibles</h3>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                    @php
                        $modulesActives = $entreprise->modules->pluck('id')->toArray();
                    @endphp
                    @foreach($modules as $module)
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center text-center shadow-sm hover:shadow-md hover:scale-105 transition transform w-28 h-32">
                            <!-- Icône -->
                            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-[#f9f6f3]">
                                @if($module->icon)
                                    <img src="{{ asset('storage/' . $module->icon) }}" alt="{{ $module->nom }}" class="w-7 h-7 object-contain">
                                @else
                                    <span class="text-xl">🧩</span>
                                @endif
                            </div>

                            <!-- Nom -->
                            <span class="text-sm font-medium text-[#3e2f24] truncate w-full mt-2">{{ $module->nom }}</span>

                            <!-- Bouton -->
                            <div class="mt-2 w-full">
                                @if($module->disponible)
                                    @if(in_array($module->id, $modulesActives))
                                        <a href="{{ route('pointsDeVente.show', [$entreprise->id, 'module_id' => $module->id]) }}" 
                                           class="block px-3 py-1 rounded-full text-xs font-medium bg-[#3e2f24] text-white hover:bg-[#5a4535] text-center">
                                            Voir
                                        </a>
                                    @else
                                        <form action="{{ route('modules.activate', $module->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="block w-full px-3 py-1 rounded-full text-xs font-medium bg-[#d8c1a8] text-[#3e2f24] hover:scale-105">
                                                Activer
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" class="block w-full px-3 py-1 rounded-full text-xs bg-gray-300 text-gray-600 cursor-not-allowed">
                                        Indisponible
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- {{-- Bouton Ajouter --}}
                <div class="flex justify-center mt-5">
                    <a href="{{ route('modules.create') }}" 
                       class="px-4 py-2 rounded-lg text-[#f9f6f3] font-semibold bg-[#3e2f24] hover:bg-[#5a4535] flex items-center gap-2 text-sm">
                        <span class="text-lg">➕</span> Ajouter un module
                    </a>
                </div> -->
            </div>
        @endif
    </div>
</x-app1-layout>

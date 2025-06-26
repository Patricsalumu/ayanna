<!-- Navigation secondaire pour la comptabilité -->
<div class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8 overflow-x-auto py-3">
            <a href="{{ route('comptabilite.journal') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('comptabilite.journal') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-book mr-2"></i>
                Journal
            </a>
            <a href="{{ route('comptabilite.grand-livre') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('comptabilite.grand-livre*') ? 'bg-green-100 text-green-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-list-alt mr-2"></i>
                Grand Livre
            </a>
            <a href="{{ route('comptabilite.balance') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('comptabilite.balance*') ? 'bg-indigo-100 text-indigo-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-balance-scale mr-2"></i>
                Balance
            </a>
            <a href="{{ route('comptabilite.bilan') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('comptabilite.bilan') ? 'bg-blue-100 text-blue-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-chart-pie mr-2"></i>
                Bilan
            </a>
            <a href="{{ route('comptabilite.compte-resultat') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('comptabilite.compte-resultat') ? 'bg-green-100 text-green-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-chart-line mr-2"></i>
                Compte de Résultat
            </a>
            <a href="{{ route('comptabilite.configuration-pdv') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('comptabilite.configuration-pdv') ? 'bg-purple-100 text-purple-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-cog mr-2"></i>
                Configuration
            </a>
        </div>
    </div>
</div>

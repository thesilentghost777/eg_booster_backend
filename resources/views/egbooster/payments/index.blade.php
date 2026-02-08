<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Paiements - EGBooster</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                            Gestion des Paiements
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">Administration EGBooster</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        <i class="far fa-clock mr-1"></i>
                        {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-gray-100 rounded-full p-3">
                            <i class="fas fa-receipt text-gray-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En attente</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Réussis</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['success'] }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Échoués</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Montant Total</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_amount'], 0, ',', ' ') }}</p>
                            <p class="text-xs text-gray-500">FCFA</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('egb.payments.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Recherche -->
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-search mr-1"></i> Rechercher
                                </label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="ID, référence, téléphone..."
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Statut -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-filter mr-1"></i> Statut
                                </label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Tous</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Réussi</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                                </select>
                            </div>

                            <!-- Méthode de paiement -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-mobile-alt mr-1"></i> Méthode
                                </label>
                                <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Toutes</option>
                                    <option value="momo" {{ request('payment_method') == 'momo' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="om" {{ request('payment_method') == 'om' ? 'selected' : '' }}>Orange Money</option>
                                </select>
                            </div>

                            <!-- Date début -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="far fa-calendar mr-1"></i> Date début
                                </label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Date fin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="far fa-calendar mr-1"></i> Date fin
                                </label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Boutons -->
                            <div class="lg:col-span-3 flex items-end gap-3">
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-filter mr-2"></i> Filtrer
                                </button>
                                <a href="{{ route('egb.payments.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                    <i class="fas fa-redo mr-2"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table des paiements -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $payment->external_id }}</div>
                                        @if($payment->freemopay_reference)
                                            <div class="text-xs text-gray-500">{{ $payment->freemopay_reference }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $payment->user->prenom ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500">ID: {{ $payment->user_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-phone mr-1 text-gray-400"></i>
                                            {{ $payment->phone_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ number_format($payment->amount_fcfa, 0, ',', ' ') }} FCFA
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($payment->payment_method == 'momo')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-mobile-alt mr-1"></i> Mobile Money
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-mobile-alt mr-1"></i> Orange Money
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($payment->status == 'pending')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i> En attente
                                            </span>
                                        @elseif($payment->status == 'success')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Réussi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Échoué
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $payment->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs">{{ $payment->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('egb.payments.show', $payment->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-eye mr-1"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                                            <p class="text-gray-500 text-lg">Aucun paiement trouvé</p>
                                            <p class="text-gray-400 text-sm mt-2">Essayez de modifier vos filtres de recherche</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($payments->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                @if($payments->onFirstPage())
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                        Précédent
                                    </span>
                                @else
                                    <a href="{{ $payments->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Précédent
                                    </a>
                                @endif

                                @if($payments->hasMorePages())
                                    <a href="{{ $payments->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Suivant
                                    </a>
                                @else
                                    <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                        Suivant
                                    </span>
                                @endif
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Affichage de
                                        <span class="font-medium">{{ $payments->firstItem() ?? 0 }}</span>
                                        à
                                        <span class="font-medium">{{ $payments->lastItem() ?? 0 }}</span>
                                        sur
                                        <span class="font-medium">{{ $payments->total() }}</span>
                                        résultats
                                    </p>
                                </div>
                                <div>
                                    {{ $payments->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-gray-500">
                © {{ date('Y') }} EGBooster - Système de gestion des paiements
            </p>
        </div>
    </footer>
</body>
</html>

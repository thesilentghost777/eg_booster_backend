<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Paiement #{{ $payment->external_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('egb.payments.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-file-invoice text-blue-600 mr-2"></i>
                                Détails du Paiement
                            </h1>
                            <p class="text-sm text-gray-600 mt-1">Référence: {{ $payment->external_id }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        <i class="far fa-clock mr-1"></i>
                        {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Colonne principale -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Informations principales -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Informations du Paiement
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">ID Interne</dt>
                                    <dd class="text-sm text-gray-900 font-mono bg-gray-50 px-3 py-2 rounded">
                                        {{ $payment->external_id }}
                                    </dd>
                                </div>

                                @if($payment->freemopay_reference)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 mb-1">Référence Freemopay</dt>
                                        <dd class="text-sm text-gray-900 font-mono bg-gray-50 px-3 py-2 rounded">
                                            {{ $payment->freemopay_reference }}
                                        </dd>
                                    </div>
                                @endif

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Montant</dt>
                                    <dd class="text-2xl font-bold text-gray-900">
                                        {{ number_format($payment->amount_fcfa, 0, ',', ' ') }}
                                        <span class="text-sm font-normal text-gray-500">FCFA</span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Méthode de Paiement</dt>
                                    <dd>
                                        @if($payment->payment_method == 'momo')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-mobile-alt mr-2"></i> Mobile Money
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-mobile-alt mr-2"></i> Orange Money
                                            </span>
                                        @endif
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Numéro de Téléphone</dt>
                                    <dd class="text-sm text-gray-900">
                                        <i class="fas fa-phone mr-2 text-gray-400"></i>
                                        {{ $payment->phone_number }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Statut</dt>
                                    <dd>
                                        @if($payment->status == 'pending')
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-2"></i> En attente
                                            </span>
                                        @elseif($payment->status == 'success')
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-2"></i> Réussi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-2"></i> Échoué
                                            </span>
                                        @endif
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Date de Création</dt>
                                    <dd class="text-sm text-gray-900">
                                        <i class="far fa-calendar mr-2 text-gray-400"></i>
                                        {{ $payment->created_at->format('d/m/Y à H:i:s') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Dernière Mise à Jour</dt>
                                    <dd class="text-sm text-gray-900">
                                        <i class="far fa-clock mr-2 text-gray-400"></i>
                                        {{ $payment->updated_at->format('d/m/Y à H:i:s') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Message d'échec -->
                    @if($payment->status == 'failed' && $payment->failure_message)
                        <div class="bg-red-50 border-l-4 border-red-400 rounded-lg shadow">
                            <div class="p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800 mb-2">Message d'Erreur</h3>
                                        <p class="text-sm text-red-700">{{ $payment->failure_message }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Métadonnées -->
                    @if($payment->metadata)
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-database mr-2 text-blue-600"></i>
                                    Métadonnées
                                </h2>
                            </div>
                            <div class="p-6">
                                <pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Colonne latérale -->
                <div class="space-y-6">
                    <!-- Informations utilisateur -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-user mr-2 text-blue-600"></i>
                                Utilisateur
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-center mb-4">
                                <div class="h-20 w-20 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600 text-3xl"></i>
                                </div>
                            </div>
                            <div class="text-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $payment->user->prenom ?? 'N/A' }}
                                </h3>
                                <p class="text-sm text-gray-500">ID: {{ $payment->user_id }}</p>
                            </div>
                            @if($payment->user)
                                <div class="space-y-3 pt-4 border-t border-gray-200">
                                    @if($payment->user->email)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-envelope w-5 text-gray-400"></i>
                                            <span class="ml-2">{{ $payment->user->email }}</span>
                                        </div>
                                    @endif
                                    @if($payment->user->telephone)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-phone w-5 text-gray-400"></i>
                                            <span class="ml-2">{{ $payment->user->telephone }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-coins w-5 text-gray-400"></i>
                                        <span class="ml-2">Solde: {{ number_format($payment->user->points_balance ?? 0, 0, ',', ' ') }} points</span>
                                    </div>
                                    @if($payment->user->referral_code)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-share-alt w-5 text-gray-400"></i>
                                            <span class="ml-2 font-mono">{{ $payment->user->referral_code }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-bolt mr-2 text-blue-600"></i>
                                Actions Rapides
                            </h2>
                        </div>
                        <div class="p-6 space-y-3">
                            <button onclick="window.print()" class="w-full flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                <i class="fas fa-print mr-2"></i>
                                Imprimer
                            </button>
                            <button onclick="copyToClipboard('{{ $payment->external_id }}')" class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-copy mr-2"></i>
                                Copier l'ID
                            </button>
                            <a href="{{ route('egb.payments.index') }}" class="w-full flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-list mr-2"></i>
                                Voir tous les paiements
                            </a>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-history mr-2 text-blue-600"></i>
                                Historique
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <li class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <i class="fas fa-plus text-white text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">Paiement créé</p>
                                                    <p class="text-xs text-gray-500">{{ $payment->created_at->diffForHumans() }}</p>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-600">{{ $payment->created_at->format('d/m/Y à H:i:s') }}</p>
                                            </div>
                                        </div>
                                    </li>
                                    @if($payment->created_at != $payment->updated_at)
                                        <li class="relative">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full {{ $payment->status == 'success' ? 'bg-green-500' : ($payment->status == 'failed' ? 'bg-red-500' : 'bg-yellow-500') }} flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas {{ $payment->status == 'success' ? 'fa-check' : ($payment->status == 'failed' ? 'fa-times' : 'fa-clock') }} text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">Statut: {{ ucfirst($payment->status) }}</p>
                                                        <p class="text-xs text-gray-500">{{ $payment->updated_at->diffForHumans() }}</p>
                                                    </div>
                                                    <p class="mt-1 text-xs text-gray-600">{{ $payment->updated_at->format('d/m/Y à H:i:s') }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
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

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('ID copié dans le presse-papiers !');
            }, function(err) {
                alert('Erreur lors de la copie : ' + err);
            });
        }
    </script>
</body>
</html>

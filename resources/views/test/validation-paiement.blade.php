<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Validation Paiement</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 200px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Test de Validation des Paiements</h1>
    
    <div x-data="{
        form: {
            point_de_vente_id: '1',
            table_id: '1',
            mode_paiement: '',
            client_id: '',
            serveuse_id: ''
        },
        result: null,
        loading: false,
        
        async testValidation() {
            this.loading = true;
            this.result = null;
            
            try {
                const response = await fetch('/test-validation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.result = { type: 'success', data: data };
                } else {
                    this.result = { type: 'error', data: data };
                }
            } catch (error) {
                this.result = { type: 'error', data: { error: 'Erreur de connexion' } };
            } finally {
                this.loading = false;
            }
        }
    }">
        <form @submit.prevent="testValidation()">
            <div class="form-group">
                <label>Mode de paiement :</label>
                <select x-model="form.mode_paiement" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="especes">Espèces</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="compte_client">Compte Client</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Client ID (optionnel sauf pour compte client) :</label>
                <input type="text" x-model="form.client_id" placeholder="Ex: 1">
            </div>
            
            <div class="form-group">
                <label>Serveuse ID (optionnel sauf pour compte client) :</label>
                <input type="text" x-model="form.serveuse_id" placeholder="Ex: 1">
            </div>
            
            <button type="submit" :disabled="loading">
                <span x-show="!loading">Tester la validation</span>
                <span x-show="loading">Test en cours...</span>
            </button>
        </form>
        
        <div x-show="result" class="result" :class="result?.type === 'success' ? 'success' : 'error'">
            <h3 x-text="result?.type === 'success' ? 'Succès' : 'Erreur'"></h3>
            <div x-show="result?.type === 'success'">
                <p><strong>Message :</strong> <span x-text="result?.data?.message"></span></p>
                <p><strong>Mode de paiement :</strong> <span x-text="result?.data?.mode_paiement"></span></p>
                <p><strong>Type de validation :</strong> <span x-text="result?.data?.validation_type"></span></p>
            </div>
            <div x-show="result?.type === 'error'">
                <p><strong>Erreur :</strong> <span x-text="result?.data?.error"></span></p>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <h3>Instructions de test :</h3>
            <ul>
                <li><strong>Espèces/Mobile Money :</strong> Vous pouvez valider sans client ni serveuse</li>
                <li><strong>Compte Client :</strong> Client ID et Serveuse ID sont obligatoires</li>
            </ul>
        </div>
    </div>
</body>
</html>

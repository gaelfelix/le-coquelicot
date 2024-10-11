document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const specializationSelect = document.getElementById('specialization');

    roleSelect.addEventListener('change', function() {
        const role = this.value;

        // Réinitialiser les options de spécialisation
        specializationSelect.innerHTML = '<option value="">Sélectionnez une spécialisation</option>';

        // Spécialisations disponibles en fonction du rôle
        let specializations = [];
        if (role === 'ARTISTE') {
            specializations = [
                { id: 10, name: 'Comédien' },
                { id: 11, name: 'Musicien' },
                { id: 12, name: 'Chanteur' },
                { id: 13, name: 'Troupe' },
                { id: 14, name: 'Danseur' },
                { id: 15, name: 'Compagnie' }
            ];
        } else if (role === 'PRO') {
            specializations = [
                { id: 1, name: 'Manager' },
                { id: 2, name: 'Tourneur' },
                { id: 3, name: 'Producteur' },
                { id: 4, name: 'Technicien' },
                { id: 5, name: 'Régisseur' },
                { id: 6, name: 'Festival' },
                { id: 7, name: 'Association' },
                { id: 8, name: 'Média' },
                { id: 9, name: 'Organisateur' }
            ];
        }

        // Ajouter les options de spécialisation au sélecteur
        specializations.forEach(specialization => {
            const option = document.createElement('option');
            option.value = specialization.id;
            option.textContent = specialization.name;
            specializationSelect.appendChild(option);
        });
    });
});
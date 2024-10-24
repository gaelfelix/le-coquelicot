document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const specializationSelect = document.getElementById('specialization');

    // Define specializations for each role
    const specializations = {
        ARTISTE: [
            { id: 10, name: 'Comédien' },
            { id: 11, name: 'Musicien' },
            { id: 12, name: 'Chanteur' },
            { id: 13, name: 'Troupe' },
            { id: 14, name: 'Danseur' },
            { id: 15, name: 'Compagnie' }
        ],
        PRO: [
            { id: 1, name: 'Manager' },
            { id: 2, name: 'Tourneur' },
            { id: 3, name: 'Producteur' },
            { id: 4, name: 'Technicien' },
            { id: 5, name: 'Régisseur' },
            { id: 6, name: 'Festival' },
            { id: 7, name: 'Association' },
            { id: 8, name: 'Média' },
            { id: 9, name: 'Organisateur' }
        ]
    };

    // Update specialization options based on selected role
    roleSelect.addEventListener('change', function() {
        const role = this.value;
        specializationSelect.innerHTML = '<option value="">Sélectionnez une spécialisation</option>';
        
        if (specializations[role]) {
            specializations[role].forEach(spec => {
                const option = document.createElement('option');
                option.value = spec.id;
                option.textContent = spec.name;
                specializationSelect.appendChild(option);
            });
        }
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const specializationSelect = document.getElementById('specialization');

    // Define specializations for each role
    const specializations = {
        ARTISTE: [
            { id: 10, name: 'Comedian' },
            { id: 11, name: 'Musician' },
            { id: 12, name: 'Singer' },
            { id: 13, name: 'Troupe' },
            { id: 14, name: 'Dancer' },
            { id: 15, name: 'Company' }
        ],
        PRO: [
            { id: 1, name: 'Manager' },
            { id: 2, name: 'Booker' },
            { id: 3, name: 'Producer' },
            { id: 4, name: 'Technician' },
            { id: 5, name: 'Stage Manager' },
            { id: 6, name: 'Festival' },
            { id: 7, name: 'Association' },
            { id: 8, name: 'Media' },
            { id: 9, name: 'Organizer' }
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers with min date as today
    const today = new Date().toISOString().split('T')[0];
    
    // Set min dates for all date inputs
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.min = today;
        
        // For return/check-out dates, set min based on departure/check-in
        if (input.id === 'return-date' || input.id === 'check-out' || input.id === 'dropoff-date') {
            const correspondingDateInput = input.id === 'return-date' ? 'departure-date' : 
                                        input.id === 'check-out' ? 'check-in' : 'pickup-date';
            
            document.getElementById(correspondingDateInput).addEventListener('change', function() {
                input.min = this.value;
            });
        }
    });
    
    // Simple form validation for search forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            // Check required fields
            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Remove validation classes when user starts typing
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});
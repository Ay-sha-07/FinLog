document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', (e) => {
            const amount = document.querySelector('input[name="amount"]').value;
            
            // Basic Validation: Ensure amount is greater than zero
            if (amount <= 0) {
                e.preventDefault(); // Stops the form from submitting
                alert("Please enter an amount greater than zero.");
            }
        });
    }
});
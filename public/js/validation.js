document.addEventListener("DOMContentLoaded", function() {
    
    // Validation Formulaire Activité (Création et Modification)
    const formActivite = document.querySelector(".js-validate-activite");
    if (formActivite) {
        formActivite.addEventListener("submit", function(e) {
            let isValid = true;
            
            const nomInput = formActivite.querySelector('input[name="nom_activite"]');
            const dureeInput = formActivite.querySelector('input[name="duree_minutes"]');
            const calInput = formActivite.querySelector('input[name="calories_brulees"]');
            
            const nomError = formActivite.querySelector('.nom-error');
            const dureeError = formActivite.querySelector('.duree-error');
            const calError = formActivite.querySelector('.cal-error');

            // Reset errors
            nomInput.classList.remove('input-error'); nomError.style.display = 'none';
            dureeInput.classList.remove('input-error'); dureeError.style.display = 'none';
            calInput.classList.remove('input-error'); calError.style.display = 'none';

            if (nomInput.value.trim() === "") {
                nomInput.classList.add('input-error');
                nomError.style.display = 'block';
                isValid = false;
            }

            if (dureeInput.value.trim() === "" || isNaN(dureeInput.value) || parseInt(dureeInput.value) <= 0) {
                dureeInput.classList.add('input-error');
                dureeError.style.display = 'block';
                isValid = false;
            }

            if (calInput.value.trim() === "" || isNaN(calInput.value) || parseInt(calInput.value) < 0) {
                calInput.classList.add('input-error');
                calError.style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Stop submission
            }
        });
    }

    // Validation Formulaire Exercice
    const formExercice = document.querySelector(".js-validate-exercice");
    if (formExercice) {
        formExercice.addEventListener("submit", function(e) {
            let isValid = true;
            
            const nomInput = formExercice.querySelector('input[name="nom_exercice"]');
            const seriesInput = formExercice.querySelector('input[name="series"]');
            const repInput = formExercice.querySelector('input[name="repetitions"]');
            
            const nomError = formExercice.querySelector('.nom-error');
            const numError = formExercice.querySelector('.num-error');
            const numError2 = formExercice.querySelector('.num-error2');

            // Reset errors
            nomInput.classList.remove('input-error'); nomError.style.display = 'none';
            seriesInput.classList.remove('input-error'); numError.style.display = 'none';
            repInput.classList.remove('input-error'); numError2.style.display = 'none';

            if (nomInput.value.trim() === "") {
                nomInput.classList.add('input-error');
                nomError.style.display = 'block';
                isValid = false;
            }

            if (seriesInput.value.trim() === "" || isNaN(seriesInput.value) || parseInt(seriesInput.value) <= 0) {
                seriesInput.classList.add('input-error');
                numError.style.display = 'block';
                isValid = false;
            }

            if (repInput.value.trim() === "" || isNaN(repInput.value) || parseInt(repInput.value) <= 0) {
                repInput.classList.add('input-error');
                numError2.style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

});

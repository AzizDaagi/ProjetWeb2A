document.addEventListener("DOMContentLoaded", function() {
    
    const isOnlyNumbers = (str) => /^\d+$/.test(str);
    const isPositiveNumber = (str) => !isNaN(str) && parseFloat(str) > 0;

    // Validation Formulaire Activité (Création et Modification)
    const formActivite = document.querySelector(".js-validate-activite");
    if (formActivite) {
        formActivite.addEventListener("submit", function(e) {
            let isValid = true;
            
            const nomInput = formActivite.querySelector('input[name="nom_activite"]');
            const dureeInput = formActivite.querySelector('input[name="duree_minutes"]');
            const calInput = formActivite.querySelector('input[name="calories_brulees"]');
            const descInput = formActivite.querySelector('textarea[name="description"]');
            
            const nomError = formActivite.querySelector('.nom-error');
            const dureeError = formActivite.querySelector('.duree-error');
            const calError = formActivite.querySelector('.cal-error');
            const descError = formActivite.querySelector('.desc-error');

            // Reset errors
            [nomInput, dureeInput, calInput, descInput].forEach(input => {
                if(input) input.classList.remove('input-error');
            });
            [nomError, dureeError, calError, descError].forEach(err => {
                if(err) err.style.display = 'none';
            });

            // Nom: not empty, not only numbers
            if (nomInput.value.trim() === "" || isOnlyNumbers(nomInput.value.trim())) {
                nomInput.classList.add('input-error');
                nomError.style.display = 'block';
                isValid = false;
            }

            // Durée: numbers only, positive
            if (dureeInput.value.trim() === "" || !isPositiveNumber(dureeInput.value.trim())) {
                dureeInput.classList.add('input-error');
                dureeError.style.display = 'block';
                isValid = false;
            }

            // Calories: numbers only, positive or zero
            if (calInput.value.trim() === "" || isNaN(calInput.value.trim()) || parseFloat(calInput.value.trim()) < 0) {
                calInput.classList.add('input-error');
                calError.style.display = 'block';
                isValid = false;
            }

            // Description: not empty
            if (descInput && descInput.value.trim() === "") {
                descInput.classList.add('input-error');
                if(descError) descError.style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
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
            const muscleInput = formExercice.querySelector('input[name="muscle_principal"]');
            
            const nomError = formExercice.querySelector('.nom-error');
            const numError = formExercice.querySelector('.num-error');
            const numError2 = formExercice.querySelector('.num-error2');

            // Reset errors
            [nomInput, seriesInput, repInput, muscleInput].forEach(input => {
                if(input) input.classList.remove('input-error');
            });
            [nomError, numError, numError2].forEach(err => {
                if(err) err.style.display = 'none';
            });

            if (nomInput.value.trim() === "" || isOnlyNumbers(nomInput.value.trim())) {
                nomInput.classList.add('input-error');
                nomError.style.display = 'block';
                isValid = false;
            }

            if (seriesInput.value.trim() === "" || !isPositiveNumber(seriesInput.value.trim())) {
                seriesInput.classList.add('input-error');
                numError.style.display = 'block';
                isValid = false;
            }

            if (repInput.value.trim() === "" || !isPositiveNumber(repInput.value.trim())) {
                repInput.classList.add('input-error');
                numError2.style.display = 'block';
                isValid = false;
            }

            if (muscleInput && (muscleInput.value.trim() === "" || isOnlyNumbers(muscleInput.value.trim()))) {
                muscleInput.classList.add('input-error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

});

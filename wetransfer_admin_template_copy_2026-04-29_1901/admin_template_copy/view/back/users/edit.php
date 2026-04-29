<div class="container admin-page admin-form-page">
    
    
    
    
    <h1><i class="fa-solid fa-user-pen icon"></i> Modifier un utilisateur</h1>
    <p class="subtitle"></p>

    
        <div class="alert alert-error">
            <ul>
                
                    <li></li>
                
            </ul>
        </div>
    

    <form method="POST" action="/smart_nutrition/#?action=update-user" novalidate>
        <input type="hidden" name="id" value="">

        <div class="field">
            <label><i class="fa-solid fa-tag icon"></i>Nom</label>
            <input type="text" name="nom" value="" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Prenom</label>
            <input type="text" name="prenom" value="" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-calendar icon"></i>Date de naissance</label>
            <input type="date" name="date_naissance" value="" min="1950-01-01" max="" required>
        </div>

        
            <div class="field">
                <label><i class="fa-solid fa-venus-mars icon"></i>Sexe</label>
                <select name="sexe" required>
                    <option value="">Selectionnez...</option>
                    <option value="homme" >Homme</option>
                    <option value="femme" >Femme</option>
                </select>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-hourglass-half icon"></i>Age</label>
                <input type="number" name="age" min="13" max="120" value="" readonly required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-weight-scale icon"></i>Poids (kg)</label>
                <input type="number" name="poids" min="30" max="250" step="0.01" value="" required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-ruler-vertical icon"></i>Taille (cm)</label>
                <input type="number" name="taille" min="1" max="300" step="0.01" value="">
            </div>

            <div class="field">
                <label><i class="fa-solid fa-bullseye icon"></i>Objectif</label>
                <textarea name="objectif" rows="4" required></textarea>
            </div>
        

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" name="email" value="" required>
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Enregistrer</button>
    </form>

    <div class="actions">
        <a href="/smart_nutrition/#?action=users-list" class="btn secondary">Retour a la liste</a>
    </div>
</div>



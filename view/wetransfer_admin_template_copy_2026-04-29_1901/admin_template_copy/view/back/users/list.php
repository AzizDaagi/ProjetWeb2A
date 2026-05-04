<div class="container admin-dashboard users-list-page admin-page">
    <h1><i class="fa-solid fa-users icon"></i> Utilisateurs</h1>
    <p class="subtitle">Liste complete des utilisateurs inscrits</p>

    
        <div class="alert alert-success"></div>
    

    
        <div class="alert alert-error"></div>
    

    <div class="users-tools">
        <div class="admin-search-wrap users-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="usersSearchInput" placeholder="Rechercher un utilisateur..." autocomplete="off">
        </div>

        <div class="users-tools-meta">
            <span id="usersResultsCount" class="users-results-count"> utilisateur(s)</span>
            <a href="/smart_nutrition/#?action=create-user" class="btn-admin">
                <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
            </a>
            <button type="button" class="btn-admin-secondary users-export-btn" data-users-export>
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="users-table" data-users-table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Date naissance</th>
                    <th>Sexe</th>
                    <th>Age</th>
                    <th>Poids</th>
                    <th>Taille</th>
                    <th>Objectif</th>
                    <th>E-mail</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                
                    
                    <tr data-user-row>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td> kg</td>
                        <td> cm</td>
                        <td></td>
                        <td></td>
                        <td class="users-actions">
                            <a href="/smart_nutrition/#?action=edit-user&id=" class="btn-edit">
                                <i class="fa-solid fa-pen"></i> Modifier
                            </a>
                            <form method="POST" action="/smart_nutrition/#?action=delete-user" class="inline-form" onsubmit="return confirm('Supprimer cet utilisateur ?');" novalidate>
                                <input type="hidden" name="id" value="">
                                <button type="submit" class="btn-delete-user">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    
                
                    <tr data-no-users-row>
                        <td colspan="10" class="text-center">Aucun utilisateur trouve</td>
                    </tr>
                
            </tbody>
        </table>
    </div>

    <a href="/smart_nutrition/#?action=profile" class="btn-admin-secondary">Retour au profil</a>
</div>



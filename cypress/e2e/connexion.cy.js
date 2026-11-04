describe('Connexion et création de compte', () => {
  it('affiche la page de connexion', () => {
    cy.visit('/login');
    cy.contains('h1', 'Se connecter');
    cy.contains('Se connecter avec mon compte URCA');
    cy.get('#username').should('have.attr', 'placeholder', 'prenom.nom@etudiant.univ-reims.fr');
    cy.get('#password').should('have.attr', 'type', 'password');
  });

  it('affiche une erreur avec un mauvais mot de passe', () => {
    cy.visit('/login');
    cy.get('#username').type('john@ab.cd');
    cy.get('#password').type('mauvais');
    cy.get('form[action="/login"] button[type=submit]').click();
    cy.location('pathname').should('eq', '/login');
    cy.get('[role=alert]').should('be.visible');
  });

  it('connecte un étudiant et affiche son nom dans l\'en-tête', () => {
    cy.visit('/login');
    cy.get('#username').type('john@ab.cd');
    cy.get('#password').type('abcd');
    cy.get('form[action="/login"] button[type=submit]').click();
    cy.location('pathname').should('eq', '/');
    cy.get('header').should('contain', 'John');
  });

  it('affiche la création de compte', () => {
    cy.visit('/signup');
    cy.contains('h1', 'Créer un compte');
    cy.contains('a', 'Retour à la connexion').should('have.attr', 'href', '/login');
    cy.contains('button', 'Créer mon compte');
  });

  it('reste lisible sur mobile', () => {
    cy.viewport(390, 844);
    cy.visit('/login');
    cy.get('#username').should('be.visible');
    cy.noHorizontalOverflow();
  });
});

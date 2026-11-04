describe('Admin', () => {
  beforeEach(() => {
    cy.login(Cypress.env('adminEmail'));
  });

  it('affiche le tableau de bord avec le menu renommé', () => {
    cy.visit('/admin');
    cy.contains('Tableau de bord');
    cy.get('#main-menu').within(() => {
      cy.contains('Fermetures');
      cy.contains('Matériel');
      cy.contains('Demandes');
      cy.contains('Règles de compatibilité');
    });
    cy.contains('h2', 'Demandes à traiter');
    cy.contains('h2', 'Départs et retours à venir');
    cy.contains('h2', 'Actuellement sorti');
  });

  it('ouvre le traitement d\'une demande et bascule accepter / refuser', () => {
    cy.visit('/admin');
    cy.contains('a', 'Traiter').first().click();
    cy.contains('Ta réponse');
    cy.get('#loan-comment').should('not.be.visible');
    cy.get('#loan-acceptbutton').click();
    cy.get('#loan-comment').should('be.visible').and('have.attr', 'action').and('match', /\/accept$/);
    cy.get('#loan-review-send').should('contain', 'Accepter et envoyer');
    cy.get('#loan-refusebutton').click();
    cy.get('#loan-comment').should('have.attr', 'action').and('match', /\/refuse$/);
    cy.get('#loan-comment-label').should('contain', 'Motif du refus');
  });

  it('compte les éléments cochés au retour', () => {
    cy.visit('/admin');
    cy.contains('a', 'Faire le retour').first().click();
    cy.contains('Checklist de retour');
    cy.get('[name^="return_loan[equipment]"]').first().check({ force: true });
    cy.get('[data-return-count]').first().should('have.text', '1');
  });

  it('affiche les listes CRUD dans le nouveau thème', () => {
    cy.visit('/admin');
    cy.get('#main-menu').contains('Matériel').click();
    cy.get('table.datagrid').should('be.visible');
    cy.get('body').should('have.css', 'font-family').and('include', 'Plus Jakarta Sans');
  });
});

describe('Accueil', () => {
  it('affiche le titre, les catégories et le planning pour un visiteur', () => {
    cy.visit('/');
    cy.contains('h1', 'Quel matériel veux-tu emprunter ?');
    cy.contains('a', 'Se connecter').should('have.attr', 'href', '/login');
    cy.contains('h2', 'Catégories');
    cy.get('a[href="/form/audiovisual"]').should('exist');
    cy.get('a[href="/form/vr"]').should('exist');
    cy.get('a[href="/form/graphic_design"]').should('exist');
    cy.get('[data-planning="desktop"]').should('be.visible');
    cy.get('[data-planning="mobile"]').should('not.be.visible');
    cy.get('footer').should('contain', 'Rafael Teixeira').find('a[href="https://www.rafaelteixeira.fr"]').should('exist');
  });

  it('affiche le prochain retrait et les demandes quand on est connecté', () => {
    cy.login('john@ab.cd');
    cy.visit('/');
    cy.contains('Bonjour John');
    cy.contains('h2', 'Mes demandes');
    cy.contains('Bon à savoir');
    cy.contains('1 semaine en VR et tablettes');
  });

  context('mobile', () => {
    beforeEach(() => cy.viewport(390, 844));

    it('montre la barre d\'onglets et le planning jour par jour', () => {
      cy.visit('/');
      cy.get('nav[aria-label="Navigation"]').should('be.visible').within(() => {
        cy.contains('Accueil').should('have.attr', 'aria-current', 'page');
        cy.contains('Réserver');
        cy.contains('Mes emprunts');
      });
      cy.get('[data-planning="desktop"]').should('not.be.visible');
      cy.get('[data-planning-panel="0"]').should('be.visible');
      cy.get('[data-planning-day="1"]').click().should('have.attr', 'aria-pressed', 'true');
      cy.get('[data-planning-panel="1"]').should('be.visible');
      cy.get('[data-planning-panel="0"]').should('not.be.visible');
      cy.get('footer').should('not.be.visible');
      cy.noHorizontalOverflow();
    });
  });
});

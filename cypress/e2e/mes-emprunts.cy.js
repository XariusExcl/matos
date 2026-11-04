describe('Mes emprunts', () => {
  it('liste les emprunts et bascule entre en cours et historique', () => {
    cy.login('john@ab.cd');
    cy.visit('/my-loans');
    cy.contains('h1', 'Mes emprunts');
    cy.get('[data-loans-panel="current"]').should('be.visible').find('article').should('have.length.at.least', 1);
    cy.get('[data-loans-tab="past"]').click().should('have.attr', 'aria-selected', 'true');
    cy.get('[data-loans-panel="past"]').should('be.visible');
    cy.get('[data-loans-panel="current"]').should('not.be.visible');
  });

  it('propose la fiche PDF pour un emprunt confirmé', () => {
    cy.login('john@ab.cd');
    cy.visit('/my-loans');
    cy.get('a[href^="/pdf/loan_html/"]').first().invoke('removeAttr', 'target').click();
    cy.contains("Fiche d'emprunt");
    cy.contains('Matériel emprunté');
  });

  it('annule une demande en attente depuis la fenêtre de confirmation', () => {
    cy.loginStudent();
    cy.visit('/form/vr');
    cy.get('[data-main-action]').filter(':visible').first().click();
    cy.get('#group-headset label.m-equip').filter(':not(:has(input:disabled))').first().click();
    cy.get('[data-main-action]').filter(':visible').first().click();
    cy.get('[data-step="3"] textarea').type('Test annulation');
    cy.get('[data-main-action]').filter(':visible').first().click();
    cy.get('#confirmation_modal').contains('button', 'Valider').click();
    cy.visit('/my-loans');
    cy.get('[data-loans-panel="current"] article').first().within(() => {
      cy.contains('En attente');
      cy.get('button[title="Annuler"]').click();
    });
    cy.get('dialog[open]').should('contain', 'Annuler cette demande ?').contains('Oui, annuler').click();
    cy.get('[role=alert]').should('contain', 'Le prêt a bien été annulé.');
  });

  it('s\'affiche sans débordement sur mobile', () => {
    cy.viewport(390, 844);
    cy.login('john@ab.cd');
    cy.visit('/my-loans');
    cy.get('nav[aria-label="Navigation"]').contains('Mes emprunts').should('have.attr', 'aria-current', 'page');
    cy.noHorizontalOverflow();
  });
});

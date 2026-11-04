const mainAction = () => cy.get('[data-main-action]').filter(':visible').first();

describe('Formulaire d\'emprunt', () => {
  beforeEach(() => {
    cy.loginStudent();
  });

  it('pilote les listes du formulaire avec les boutons jour et créneau', () => {
    cy.visit('/form/audiovisual');
    cy.contains('h1', 'Emprunt audiovisuel');
    cy.get('#audiovisual_loan_startDay').should('not.be.visible');
    cy.get('[data-choices-for="audiovisual_loan_startDay"] button').should('have.length.at.least', 5);
    cy.get('[data-choices-for="audiovisual_loan_startDay"] button:not(:disabled)').eq(1).invoke('attr', 'aria-label').then((label) => {
      cy.get('[data-choices-for="audiovisual_loan_startDay"] button:not(:disabled)').eq(1).click();
      cy.get(`[data-choices-for="audiovisual_loan_startDay"] button[aria-label="${label}"]`).should('have.attr', 'aria-pressed', 'true');
      cy.get('#audiovisual_loan_startDay option:selected').should('have.text', label);
    });
    cy.get('[data-choices-for="audiovisual_loan_startTimeSlot"] button').contains('12h30').click();
    cy.get('#audiovisual_loan_startTimeSlot').should('have.value', '1230');
  });

  it('refuse de passer à l\'étape 3 sans matériel', () => {
    cy.visit('/form/audiovisual');
    mainAction().click();
    cy.get('[data-step="2"] [data-step-body]').should('be.visible');
    mainAction().click();
    cy.get('#error_modal').should('have.attr', 'open');
    cy.get('#error_modal_content').should('contain', 'Votre emprunt est vide');
  });

  it('envoie une demande audiovisuelle en 3 étapes', () => {
    cy.visit('/form/audiovisual');
    cy.get('[data-step-indicator="1"] [data-step-label]').should('have.class', 'text-ink-900');
    mainAction().click();
    cy.get('[data-step="1"] [data-step-summary]').should('be.visible').and('contain', '→');
    cy.get('#group-cameras label.m-equip').filter(':not(:has(input:disabled))').filter(':has(input:not([value=""]))').first().click();
    cy.get('aside [data-recap-items] li').should('have.length', 1).and('not.have.class', 'text-ink-400');
    mainAction().click();
    cy.get('[data-step="3"] textarea').type('Tournage SAÉ 501, tel 06 00 00 00 00');
    mainAction().should('contain', 'Envoyer la demande').click();
    cy.get('#confirmation_modal').should('have.attr', 'open');
    cy.get('#confirmation_modal_content').should('not.be.empty');
    cy.get('#confirmation_modal').contains('button', 'Valider').click();
    cy.location('pathname').should('eq', '/');
    cy.get('[role=alert]').should('contain', 'Votre réservation a bien été enregistrée.');
  });

  it('permet de revenir modifier une étape validée', () => {
    cy.visit('/form/vr');
    cy.contains('h1', 'Emprunt casque VR');
    cy.contains('1 semaine');
    mainAction().click();
    cy.get('[data-step="1"] [data-step-edit]').should('be.visible').click();
    cy.get('[data-step="1"] [data-step-body]').should('be.visible');
    cy.get('[data-step="2"] [data-step-body]').should('not.be.visible');
  });

  it('cale le retour sur le départ et passe au matériel sur mobile', () => {
    cy.viewport(390, 844);
    cy.visit('/form/audiovisual');
    cy.get('[data-choices-for="audiovisual_loan_startDay"] button:not(:disabled)').last().click();
    cy.get('#creneau-error').should('be.empty');
    cy.get('#audiovisual_loan_endDay').invoke('val').then((end) => {
      cy.get('#audiovisual_loan_startDay').invoke('val').should((start) => expect(parseInt(end)).to.be.at.least(parseInt(start)));
    });
    mainAction().click();
    cy.get('[data-step="2"] [data-step-body]').should('be.visible');
  });

  it('affiche l\'erreur de créneau dans la barre du bas sur mobile', () => {
    cy.viewport(390, 844);
    cy.visit('/form/audiovisual');
    cy.get('[data-choices-for="audiovisual_loan_endDay"] button:not(:disabled)').last().click();
    mainAction().click();
    cy.get('#creneau-error').should('not.be.empty');
    cy.get('.fixed [data-recap-slot]').should('have.class', '!text-error').and('not.contain', '→');
    cy.get('[data-step="2"] [data-step-body]').should('not.be.visible');
  });

  it('envoie une demande de tablette sur mobile', () => {
    cy.viewport(390, 844);
    cy.visit('/form/graphic_design');
    cy.contains('h1', 'Emprunt tablette graphique');
    cy.noHorizontalOverflow();
    mainAction().click();
    cy.get('#group-tablet label.m-equip').filter(':not(:has(input:disabled))').first().click();
    mainAction().click();
    cy.get('[data-step="3"] textarea').type('Projet d\'illustration');
    mainAction().click();
    cy.get('#confirmation_modal').contains('button', 'Valider').click();
    cy.get('[role=alert]').should('contain', 'Votre réservation a bien été enregistrée.');
  });
});

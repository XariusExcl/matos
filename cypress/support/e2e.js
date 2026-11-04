Cypress.Commands.add('login', (email, password = 'abcd') => {
  cy.session([email, password], () => {
    cy.visit('/login');
    cy.get('#username').type(email);
    cy.get('#password').type(password, { log: false });
    cy.get('form[action="/login"] button[type=submit]').click();
    cy.location('pathname').should('not.eq', '/login');
  });
});

Cypress.Commands.add('resetStudent', () => {
  cy.task('resetStudent', { email: Cypress.env('studentEmail'), hash: Cypress.env('studentHash') });
});

Cypress.Commands.add('loginStudent', () => {
  cy.resetStudent();
  cy.login(Cypress.env('studentEmail'));
});

Cypress.Commands.add('noHorizontalOverflow', () => {
  cy.document().then((doc) => {
    expect(doc.documentElement.scrollWidth, 'pas de défilement horizontal').to.be.at.most(doc.documentElement.clientWidth);
  });
});

Cypress.on('uncaught:exception', (err) => !err.message.includes('ResizeObserver'));

Cypress.on('window:load', (win) => {
  const style = win.document.createElement('style');
  style.textContent = '.sf-toolbar, .sf-minitoolbar { display: none !important; }';
  win.document.head.appendChild(style);
});

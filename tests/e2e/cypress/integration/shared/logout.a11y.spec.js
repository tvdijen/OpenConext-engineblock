context('Logout page verify a11y', () => {
  it('Logout page contains no a11y problems on load', () => {
    cy.visit('https://engine.dev.openconext.local/logout', {failOnStatusCode: false
    });
    cy.injectAxe();
    cy.checkA11y();
  });

  it('Logout page contains no html errors', () => {
    cy.visit('https://engine.dev.openconext.local/logout', {failOnStatusCode: false
    });
    cy.htmlvalidate();
  });
});

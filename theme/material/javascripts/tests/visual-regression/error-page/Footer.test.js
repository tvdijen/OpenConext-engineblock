const timeout = 15000;
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

const footerDifferences = [
    {
        name: 'all-buttons-visible',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"http://mock-idp"}'
    },
    {
        name: 'only-support-email-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message'
    },
    {
        name: 'only-wiki-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"http://mock-idp"}'
    },
    {
        name: 'support-email-and-wiki-button-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields'
    },
];

const viewports = [
    {width: 375, height: 667},
    {width: 1920, height: 1080},
];

describe(
    'Verify',
    () => {
        let page;

        beforeAll(async () => {
            page = await global.__BROWSER__.newPage();
            jest.setTimeout(20000);
        }, timeout);

        let sets = [];
        for (const footerDifference of footerDifferences) {
            for (const viewport of viewports) {
                sets.push({
                    name: footerDifference.name,
                    url: footerDifference.url,
                    viewport: viewport,
                    expect: expect,
                    page: page,
                });
            }
        }

        for (const s of sets) {
            it(`${s.name}-${s.viewport.width}x${s.viewport.height}`, async () => {
                await s.page.goto(s.url);
                await s.page.setViewport(s.viewport);
                await s.page.waitFor(".error-container");
                const screenshot = await s.page.screenshot({
                    fullPage: true,
                    path: `./material/javascripts/tests/visual-regression/error-page/screenshots/footer/${s.name}-${s.viewport.width}x${s.viewport.height}.png`
                });
                s.expect(screenshot).toMatchImageSnapshot();
            });
        }
    },
    timeout,
);

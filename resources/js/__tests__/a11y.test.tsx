import { render } from '@testing-library/react';
import { axe, toHaveNoViolations } from 'jest-axe';
import type { ComponentProps, ReactNode } from 'react';
import { beforeAll, describe, expect, it, vi } from 'vitest';

/**
 * Automated accessibility audit (Task 13.4) covering representative public and
 * admin UI subtrees. Maps to Requirement 16:
 *
 *   - Req 16.3 (form-control labels) — AUTOMATED here. axe flags inputs without
 *     an accessible name (label/aria-label/aria-labelledby), so these checks
 *     are meaningful in jsdom.
 *   - Req 16.2 (visible focus on interactive elements) — PARTIALLY automated.
 *     axe + jsdom cannot evaluate rendered `:focus-visible` outlines, so we
 *     assert the structural prerequisites instead: interactive elements expose
 *     the correct roles/names and are natively focusable (a/button/input).
 *     Actual focus-ring visibility MUST be verified manually — see notes below.
 *   - Req 16.4 (color contrast >= 4.5:1) — NOT computable in jsdom. jsdom does
 *     not lay out or paint, and Tailwind tokens resolve to CSS variables that
 *     are never applied, so axe's `color-contrast` rule is disabled here and
 *     contrast MUST be verified manually — see notes below.
 *
 * MANUAL VERIFICATION FOLLOW-UPS (tracked outside code, per task scope):
 *   1. Color contrast (Req 16.4): verify text/background ratios >= 4.5:1 (normal
 *      text) for the Publication palette (brand #A855F7 / text-brand-strong,
 *      neutral text shades, destructive red) in light and dark themes using a
 *      contrast checker or the browser a11y/axe DevTools extension.
 *   2. Visible focus (Req 16.2): tab through public nav + admin forms in a real
 *      browser and confirm the global focus-visible ring (defined in app.css)
 *      is clearly visible on every interactive element in both themes.
 *   3. Screen-reader pass: smoke-test key flows with a real AT (NVDA/VoiceOver)
 *      to confirm announced names/roles match intent.
 */

expect.extend(toHaveNoViolations);

// jsdom cannot compute layout/paint, so the color-contrast rule would only ever
// produce false "incomplete"/false-negative results. Disable it for every audit
// and rely on the documented manual follow-up instead (Req 16.4).
const axeOptions = {
    rules: {
        'color-contrast': { enabled: false },
    },
} as const;

// Mock @inertiajs/react so layout subtrees that use <Link>/usePage can render
// in jsdom without an Inertia app context. Link -> plain anchor, usePage -> a
// minimal stub exposing the current url used by active-link logic.
vi.mock('@inertiajs/react', () => ({
    Link: ({
        href,
        children,
        ...props
    }: ComponentProps<'a'> & { href?: string; children?: ReactNode }) => (
        <a href={typeof href === 'string' ? href : '#'} {...props}>
            {children}
        </a>
    ),
    usePage: () => ({ url: '/', props: {} }),
}));

beforeAll(() => {
    // jsdom does not implement matchMedia; some UI primitives read it.
    if (!window.matchMedia) {
        Object.defineProperty(window, 'matchMedia', {
            writable: true,
            value: (query: string) => ({
                matches: false,
                media: query,
                onchange: null,
                addListener: vi.fn(),
                removeListener: vi.fn(),
                addEventListener: vi.fn(),
                removeEventListener: vi.fn(),
                dispatchEvent: vi.fn(),
            }),
        });
    }
});

describe('Accessibility audit (Req 16.2, 16.3, 16.4)', () => {
    it('public layout subtree has no detectable a11y violations (labels/roles)', async () => {
        const { default: PublicLayout } = await import(
            '@/layouts/public-layout'
        );

        const { container } = render(
            <PublicLayout>
                <h1>Skrining Thalassemia</h1>
                <p>Konten halaman publik.</p>
            </PublicLayout>,
        );

        const results = await axe(container, axeOptions);
        expect(results).toHaveNoViolations();
    });

    it('public navigation links are focusable anchors with accessible names (Req 16.2/16.3)', async () => {
        const { default: PublicLayout } = await import(
            '@/layouts/public-layout'
        );

        const { getByRole } = render(
            <PublicLayout>
                <p>Konten.</p>
            </PublicLayout>,
        );

        // Native <a href> is keyboard-focusable; an accessible name is present.
        const nav = getByRole('navigation', { name: /navigasi utama/i });
        expect(nav).toBeInTheDocument();
        const beranda = getByRole('link', { name: /beranda/i });
        expect(beranda).toHaveAttribute('href');
    });

    it('admin form subtree has no detectable a11y violations and labels every control (Req 16.3)', async () => {
        const { Label } = await import('@/components/ui/label');
        const { Input } = await import('@/components/ui/input');
        const { Button } = await import('@/components/ui/button');
        const { default: InputError } = await import(
            '@/components/input-error'
        );

        const { container, getByLabelText } = render(
            <main>
                <h1>Kelola Data Latih</h1>
                <form aria-label="Form data latih">
                    <div>
                        <Label htmlFor="hb-ayah">Kadar HbA2 Ayah</Label>
                        <Input
                            id="hb-ayah"
                            name="hb_ayah"
                            type="text"
                            aria-describedby="hb-ayah-error"
                        />
                        <InputError
                            id="hb-ayah-error"
                            message="Wajib diisi."
                        />
                    </div>
                    <div>
                        <Label htmlFor="fenotipe">Fenotipe</Label>
                        <Input id="fenotipe" name="fenotipe" type="text" />
                    </div>
                    <Button type="submit">Simpan</Button>
                </form>
            </main>,
        );

        // Each control is reachable by its accessible label (Req 16.3).
        expect(getByLabelText('Kadar HbA2 Ayah')).toBeInTheDocument();
        expect(getByLabelText('Fenotipe')).toBeInTheDocument();

        const results = await axe(container, axeOptions);
        expect(results).toHaveNoViolations();
    });

    it('PageState status indicators expose correct roles with no a11y violations (Req 16.2/16.3)', async () => {
        const { default: PageState } = await import(
            '@/components/page-state'
        );

        // error state -> role="alert"
        const errorRender = render(
            <PageState error="Gagal memuat data." isLoading={false} />,
        );
        expect(errorRender.getByRole('alert')).toHaveTextContent(
            'Gagal memuat data.',
        );
        expect(await axe(errorRender.container, axeOptions)).toHaveNoViolations();
        errorRender.unmount();

        // loading state -> role="status"
        const loadingRender = render(<PageState isLoading />);
        expect(loadingRender.getByRole('status')).toBeInTheDocument();
        expect(
            await axe(loadingRender.container, axeOptions),
        ).toHaveNoViolations();
    });
});

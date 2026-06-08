// Makes the @testing-library/jest-dom custom matchers (toBeInTheDocument,
// toHaveAttribute, toHaveTextContent, ...) available on Vitest's `expect`
// during type-checking. The runtime extension is wired up in vitest.setup.ts.
import '@testing-library/jest-dom/vitest';

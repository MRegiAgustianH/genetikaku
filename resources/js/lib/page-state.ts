/**
 * Single source of truth for page status resolution.
 *
 * Maps a set of page inputs to EXACTLY one status, guaranteeing that no two
 * status indicators can ever render simultaneously (Req 16.7).
 *
 * Precedence order (total and deterministic):
 *   error > loading > empty > ready
 *
 * Rationale: an error must always be surfaced first because the underlying data
 * cannot be trusted; an in-flight request (loading) takes precedence over a
 * provisional "empty" reading because the empty state may not be final until the
 * request settles; "empty" outranks "ready" because there is nothing to display;
 * otherwise the page is "ready".
 *
 * The function is total: for any combination of inputs it returns exactly one
 * value of {@link PageStateStatus}. Inputs are coerced so that `undefined`
 * behaves like `false`/no-error, and any non-null/non-undefined `error` value is
 * treated as an error.
 */
export type PageStateStatus = 'loading' | 'empty' | 'error' | 'ready';

export interface PageStateInput {
    /** Whether the page data is currently being fetched. */
    isLoading?: boolean;
    /** Any error value. `null`/`undefined` means "no error". */
    error?: unknown;
    /** Whether the resolved data set is empty. */
    isEmpty?: boolean;
}

/**
 * Returns true when the provided error value represents an actual error.
 * `null` and `undefined` are treated as "no error"; every other value
 * (including empty strings or `0`, which a caller deliberately passed) counts
 * as an error so that the page surfaces it.
 */
function hasError(error: unknown): boolean {
    return error !== null && error !== undefined;
}

/**
 * Resolve page inputs to exactly one status using the documented precedence
 * order: error > loading > empty > ready.
 */
export function resolvePageState(input: PageStateInput): PageStateStatus {
    if (hasError(input.error)) {
        return 'error';
    }

    if (input.isLoading === true) {
        return 'loading';
    }

    if (input.isEmpty === true) {
        return 'empty';
    }

    return 'ready';
}

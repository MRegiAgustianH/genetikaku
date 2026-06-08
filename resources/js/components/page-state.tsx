import { Loader2Icon } from 'lucide-react';
import type { ReactNode } from 'react';
import {
    resolvePageState,
    type PageStateInput,
    type PageStateStatus,
} from '@/lib/page-state';
import { cn } from '@/lib/utils';

export interface PageStateProps extends PageStateInput {
    /** Rendered when status resolves to `loading`. Falls back to a default spinner. */
    loadingSlot?: ReactNode;
    /** Rendered when status resolves to `empty`. Falls back to a default message. */
    emptySlot?: ReactNode;
    /** Rendered when status resolves to `error`. Falls back to a default alert. */
    errorSlot?: ReactNode;
    /** Rendered when status resolves to `ready`. */
    children?: ReactNode;
    /** Optional className applied to the wrapper element. */
    className?: string;
}

/**
 * Generic, reusable status gate built on top of {@link resolvePageState}.
 *
 * Renders EXACTLY ONE of the loading / empty / error / ready indicators based
 * on the single source of truth in `resolvePageState`, so conflicting status
 * indicators can never appear at once (Req 16.7).
 *
 * Accessible defaults: the loading indicator uses `role="status"` and the error
 * indicator uses `role="alert"`.
 */
export default function PageState({
    isLoading,
    error,
    isEmpty,
    loadingSlot,
    emptySlot,
    errorSlot,
    children,
    className,
}: PageStateProps) {
    const status: PageStateStatus = resolvePageState({
        isLoading,
        error,
        isEmpty,
    });

    return <div className={cn(className)}>{renderStatus(status)}</div>;

    function renderStatus(current: PageStateStatus): ReactNode {
        switch (current) {
            case 'error':
                return (
                    errorSlot ?? (
                        <div role="alert" className="text-sm text-destructive">
                            {toMessage(error) ?? 'Something went wrong.'}
                        </div>
                    )
                );
            case 'loading':
                return (
                    loadingSlot ?? (
                        <div
                            role="status"
                            aria-live="polite"
                            className="flex items-center gap-2 text-sm text-muted-foreground"
                        >
                            <Loader2Icon
                                className="size-4 animate-spin"
                                aria-hidden="true"
                            />
                            <span>Loading…</span>
                        </div>
                    )
                );
            case 'empty':
                return (
                    emptySlot ?? (
                        <div className="text-sm text-muted-foreground">
                            No data available.
                        </div>
                    )
                );
            case 'ready':
            default:
                return children ?? null;
        }
    }
}

/** Extract a human-readable message from an arbitrary error value. */
function toMessage(error: unknown): string | undefined {
    if (error === null || error === undefined) {
        return undefined;
    }

    if (typeof error === 'string') {
        return error;
    }

    if (error instanceof Error) {
        return error.message;
    }

    if (
        typeof error === 'object' &&
        'message' in error &&
        typeof (error as { message: unknown }).message === 'string'
    ) {
        return (error as { message: string }).message;
    }

    return undefined;
}

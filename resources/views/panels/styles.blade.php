<style>
    .fi-sidebar-item-row {
        position: relative;
        width: 100%;
    }

    .fi-sidebar-item-btn-with-pin {
        width: 100%;
        min-width: 0;
        padding-inline-end: 2.5rem;
    }

    .fi-sidebar-pin-btn {
        position: absolute;
        top: 0.75rem;
        inset-inline-end: 0.75rem;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 150ms ease;
    }

    .fi-sidebar-item:hover .fi-sidebar-pin-btn,
    .fi-sidebar-item:focus-within .fi-sidebar-pin-btn {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
</style>

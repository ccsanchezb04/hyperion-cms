import Swal, { type SweetAlertOptions, type SweetAlertResult } from 'sweetalert2';

const base = Swal.mixin({
    customClass: {
        popup: 'shadow',
        confirmButton: 'btn btn-primary px-4',
        cancelButton: 'btn btn-outline-secondary px-4',
        denyButton: 'btn btn-danger px-4',
        actions: 'd-flex gap-2 justify-content-center',
    },
    buttonsStyling: false,
    reverseButtons: true,
});

export function useSwal() {
    const confirm = (options: SweetAlertOptions): Promise<SweetAlertResult> =>
        base.fire({
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            ...options,
        });

    const success = (title: string, text?: string): Promise<SweetAlertResult> =>
        base.fire({
            title,
            text,
            icon: 'success',
            timer: 2200,
            timerProgressBar: true,
            showConfirmButton: false,
        });

    const error = (title: string, text?: string): Promise<SweetAlertResult> =>
        base.fire({ title, text, icon: 'error', confirmButtonText: 'Entendido' });

    const warning = (title: string, text?: string): Promise<SweetAlertResult> =>
        base.fire({ title, text, icon: 'warning', confirmButtonText: 'Entendido' });

    return { swal: base, confirm, success, error, warning };
}

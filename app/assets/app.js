import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';
import '@fortawesome/fontawesome-free/css/regular.min.css';
import '@fortawesome/fontawesome-free/css/brands.min.css';

import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

import './styles/app.css';

const dropzones = document.querySelectorAll('[data-document-dropzone]');

dropzones.forEach((dropzone) => {
    const input = dropzone.querySelector('[data-document-dropzone-input]');
    const filename = dropzone.querySelector('[data-document-dropzone-filename]');

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    if (!(filename instanceof HTMLElement)) {
        return;
    }

    const updateFilename = () => {
        const files = input.files;

        if (!files || files.length === 0) {
            filename.textContent = 'PDF files only.';
            return;
        }

        filename.textContent = files.length === 1
            ? files[0].name
            : `${files.length} PDF documents selected.`;
    };

    input.addEventListener('change', updateFilename);

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('document-dropzone--dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('document-dropzone--dragover');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('document-dropzone--dragover');

        const files = event.dataTransfer?.files;

        if (!files || files.length === 0) {
            return;
        }

        const pdfFiles = Array.from(files).filter(
            (file) => file.type === 'application/pdf',
        );

        if (pdfFiles.length !== files.length) {
            filename.textContent = 'Please select PDF documents only.';
            return;
        }

        const dataTransfer = new DataTransfer();

        pdfFiles.forEach((file) => {
            dataTransfer.items.add(file);
        });

        input.files = dataTransfer.files;

        input.dispatchEvent(
            new Event('change', {
                bubbles: true,
            }),
        );
    });
});

const importForms = document.querySelectorAll(
    '[data-document-import-form]',
);

importForms.forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const submitButton = form.querySelector(
        '[data-document-import-submit]',
    );

    if (!(submitButton instanceof HTMLButtonElement)) {
        return;
    }
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            return;
        }

        event.preventDefault();

        submitButton.disabled = true;

        submitButton.innerHTML = `
        <span
            class="spinner-border spinner-border-sm me-2"
            aria-hidden="true"
        ></span>
        Importing…
    `;

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                form.submit();
            });
        });
    });
});

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
        const file = input.files?.[0];

        filename.textContent = file
            ? file.name
            : 'PDF files only.';
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

        const file = files[0];

        if (file.type !== 'application/pdf') {
            filename.textContent = 'Please select a PDF document.';
            return;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        input.files = dataTransfer.files;

        input.dispatchEvent(
            new Event('change', {
                bubbles: true,
            }),
        );
    });
});

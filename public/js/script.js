$(document).ready(function () {
    const form = $('#note-form');
    if (form.length > 0 && typeof Quill !== 'undefined') {
        let quill = null;
        const editable = String(form.data('editable')) === '1';
        const saveMode = String(form.data('save-mode') || 'remote');
        const draftKey = String(form.data('draft-key') || 'home_note_draft_v2');
        const editorElement = document.getElementById('data-editor');
        const dataField = $('#data');
        const titleField = $('#title');
        const saveStatus = $('#save-status');
        const doneTypingInterval = 2000;
        let typingTimer;
        let restoredLocalDraft = false;
        let activeImageResize = null;

        if (editorElement && dataField.length > 0) {
            quill = new Quill(editorElement, {
                theme: 'snow',
                placeholder: editorElement.getAttribute('data-placeholder') || 'Start writing...',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'code-block', 'link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: imageHandler
                        }
                    }
                }
            });

            if (dataField.val()) {
                quill.root.innerHTML = dataField.val();
            }

            if (saveMode === 'local' && editable && !dataField.val() && !titleField.val()) {
                restoredLocalDraft = loadLocalDraft();
            }

            if (!editable) {
                quill.disable();
                titleField.prop('disabled', true);
            }

            quill.on('text-change', function () {
                if (!editable) {
                    return;
                }

                dataField.val(quill.root.innerHTML);
                setTypingState();
            });

            titleField.on('keyup', function () {
                if (!editable) {
                    return;
                }
                setTypingState();
            });

            form.on('submit', function () {
                dataField.val(quill.root.innerHTML);
                if (saveMode === 'local') {
                    const plain = quill.getText().trim();
                    if (plain !== '') {
                        localStorage.removeItem(draftKey);
                    }
                }
            });

            if (saveMode === 'local') {
                if (restoredLocalDraft) {
                    setStatus('Local draft restored');
                } else {
                    setStatus('Start Typing');
                }
            }

            enableImageResize(quill);
        }

        function setTypingState() {
            setStatus(saveMode === 'local' ? 'Saving locally ...' : 'Saving ...');
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        }

        function doneTyping() {
            if (saveMode === 'local') {
                saveLocalDraft();
                return;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'POST',
                url: form.attr('action') || window.location.href,
                data: form.serialize(),
                success: function () {
                    setStatus('Saved');
                },
                error: function () {
                    setStatus('Save failed');
                }
            });
        }

        function saveLocalDraft() {
            try {
                localStorage.setItem(draftKey, JSON.stringify({
                    title: titleField.val() || '',
                    data: dataField.val() || '',
                    saved_at: new Date().toISOString()
                }));
                setStatus('Saved locally');
            } catch (error) {
                setStatus('Local save failed');
            }
        }

        function loadLocalDraft() {
            try {
                const raw = localStorage.getItem(draftKey);
                if (!raw) {
                    return false;
                }
                const parsed = JSON.parse(raw);
                titleField.val(parsed.title || '');
                dataField.val(parsed.data || '');
                if (parsed.data) {
                    const editor = document.getElementById('data-editor');
                    if (editor) {
                        editor.querySelector('.ql-editor').innerHTML = parsed.data;
                    }
                }
                return true;
            } catch (error) {
                return false;
            }
        }

        function setStatus(text) {
            if (saveStatus.length > 0) {
                saveStatus.text(text);
            }
        }

        function imageHandler() {
            if (!quill) {
                return;
            }

            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = function () {
                if (!input.files || !input.files[0]) {
                    return;
                }
                const file = input.files[0];
                toDataUrl(file, function (dataUrl) {
                    const range = quill.getSelection(true);
                    const insertIndex = range ? range.index : quill.getLength();
                    quill.insertEmbed(insertIndex, 'image', dataUrl, 'user');
                    quill.setSelection(insertIndex + 1, 0, 'silent');
                    setTypingState();
                });
            };
        }

        function toDataUrl(file, callback) {
            const reader = new FileReader();
            reader.onload = function (event) {
                callback(event.target.result);
            };
            reader.readAsDataURL(file);
        }

        function enableImageResize(editor) {
            const root = editor.root;
            const HANDLE_SIZE = 18;

            root.addEventListener('click', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLImageElement)) {
                    clearActiveImageResize();
                    return;
                }

                setActiveImage(target);
            });

            root.addEventListener('mousedown', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLImageElement)) {
                    return;
                }

                const rect = target.getBoundingClientRect();
                const nearRight = rect.right - event.clientX <= HANDLE_SIZE;
                const nearBottom = rect.bottom - event.clientY <= HANDLE_SIZE;

                if (!nearRight || !nearBottom) {
                    return;
                }

                event.preventDefault();
                setActiveImage(target);

                const startX = event.clientX;
                const startWidth = target.clientWidth;
                const minWidth = 80;
                const maxWidth = root.clientWidth - 20;

                function onMouseMove(moveEvent) {
                    const delta = moveEvent.clientX - startX;
                    const nextWidth = Math.max(minWidth, Math.min(maxWidth, startWidth + delta));
                    target.style.width = nextWidth + 'px';
                    target.style.height = 'auto';
                    target.setAttribute('width', String(nextWidth));
                    dataField.val(editor.root.innerHTML);
                    setTypingState();
                }

                function onMouseUp() {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                }

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            function setActiveImage(image) {
                clearActiveImageResize();
                activeImageResize = image;
                image.classList.add('editor-image-active');
                image.title = 'Drag bottom-right corner to resize';
            }

            function clearActiveImageResize() {
                if (activeImageResize) {
                    activeImageResize.classList.remove('editor-image-active');
                }
                activeImageResize = null;
            }
        }

    }

    $('[data-toggle="tooltip"]').tooltip();
});

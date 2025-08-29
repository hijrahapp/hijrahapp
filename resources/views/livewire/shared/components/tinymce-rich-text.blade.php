<div class="w-full">
    <div class="mb-1">
        {{ $slot ?? '' }}
    </div>
    <div wire:ignore>
        <textarea wire:model.defer="model" id="{{ $editorId }}_input" data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false" autocomplete="off" autocorrect="off" spellcheck="false">{!! $model !!}</textarea>
    </div>

    <script>
        (function() {
            const setup = () => {
                const input = document.getElementById(@json($editorId) + '_input');
                if (!input) return;
                if (!window.tinymce) { setTimeout(setup, 50); return; }

                const sanitize = (raw) => {
                    if (typeof raw !== 'string') return '';
                    const trimmed = raw.trim();
                    // If looks like a JSON-encoded string (e.g., "<p>..</p>") or just ""
                    if ((trimmed.startsWith('"') && trimmed.endsWith('"')) || (trimmed === '""')) {
                        try {
                            const parsed = JSON.parse(trimmed);
                            if (typeof parsed === 'string') return parsed;
                        } catch (e) { /* ignore */ }
                    }
                    return raw;
                };

                const editorId = input.id;
                const existing = window.tinymce.get(editorId);

                const rehydrate = (editor) => {
                    const html = sanitize(input.value || '');
                    if (editor && editor.initialized) {
                        editor.setContent(html || '');
                    } else {
                        input.value = html || '';
                    }
                };

                if (existing) {
                    setTimeout(() => rehydrate(existing), 0);
                    return;
                }

                const parsedHeight = (() => {
                    const match = @json($minHeight).match(/(\d+)/);
                    return match ? parseInt(match[1], 10) : 150;
                })();

                window.tinymce.init({
                    selector: '#' + editorId,
                    menubar: false,
                    branding: false,
                    plugins: 'link lists directionality',
                    toolbar: 'undo redo | bold italic underline strikethrough | bullist numlist outdent indent | ltr rtl | link removeformat',
                    placeholder: @json($placeholder),
                    height: parsedHeight,
                    setup: function (editor) {
                        editor.on('init', function () {
                            rehydrate(editor);
                        });
                        const sync = () => {
                            input.value = editor.getContent();
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        };
                        editor.on('change keyup undo redo input', sync);
                    }
                });
            };

            const trySetupNow = () => setTimeout(setup, 0);

            if (window.Livewire) {
                trySetupNow();
                Livewire.hook('morph.updated', trySetupNow);
                Livewire.on('show-modal', trySetupNow);
            } else {
                document.addEventListener('livewire:init', () => {
                    trySetupNow();
                    Livewire.hook('morph.updated', trySetupNow);
                    Livewire.on('show-modal', trySetupNow);
                });
            }
        })();
    </script>
</div>



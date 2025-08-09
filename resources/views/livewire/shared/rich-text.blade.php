<div class="w-full">
    <div class="mb-1">
        {{ $slot ?? '' }}
    </div>
    <div wire:ignore>
        <div id="{{ $editorId }}" class="border rounded bg-background" style="min-height: {{ $minHeight }}"></div>
    </div>
    <textarea wire:model.defer="model" id="{{ $editorId }}_input" class="hidden">{!! $model !!}</textarea>

    <script>
        (function() {
            const setup = () => {
                const container = document.getElementById(@json($editorId));
                if (!container) return;
                const input = document.getElementById(@json($editorId) + '_input');
                if (!input) return;

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

                // Avoid duplicate init
                if (container.__quillInstance) {
                    // Rehydrate content on re-render (delay to allow Livewire to set input values)
                    const rehydrate = () => {
                        const html = sanitize(input.value || '');
                        container.__quillInstance.root.innerHTML = html;
                    };
                    setTimeout(rehydrate, 0);
                    return;
                }

                const quill = new Quill(container, {
                    theme: 'snow',
                    placeholder: @json($placeholder),
                    modules: { toolbar: [
                        [{ header: [1, 2, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'direction': 'rtl' }],
                        ['link', 'clean']
                    ]}
                });
                container.__quillInstance = quill;

                // Initial seed from server model or input
                const seed = () => {
                    const initialHtml = sanitize(input.value || '');
                    quill.root.innerHTML = initialHtml;
                    input.value = initialHtml;
                };
                setTimeout(seed, 0);

                quill.on('text-change', function() {
                    input.value = quill.root.innerHTML;
                    input.dispatchEvent(new Event('input'));
                });
            };

            if (window.Livewire) {
                setup();
                // Rehydrate after DOM morphs
                Livewire.hook('morph.updated', setup);
                // Also handle modal show
                Livewire.on('show-modal', () => setTimeout(setup, 0));
            } else {
                document.addEventListener('livewire:init', () => {
                    setup();
                    Livewire.hook('morph.updated', setup);
                    Livewire.on('show-modal', () => setTimeout(setup, 0));
                });
            }
        })();
    </script>
</div>



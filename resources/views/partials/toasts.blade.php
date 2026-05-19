@php
    $toastItems = [];

    $flashDefinitions = [
        'success' => ['type' => 'success', 'title' => 'Success'],
        'error' => ['type' => 'danger', 'title' => 'Error'],
        'warning' => ['type' => 'warning', 'title' => 'Warning'],
        'info' => ['type' => 'info', 'title' => 'Notice'],
        'auth_notice' => ['type' => 'info', 'title' => 'Login required'],
    ];

    foreach ($flashDefinitions as $key => $definition) {
        $message = session($key);

        if (is_string($message) && trim($message) !== '') {
            $toastItems[] = $definition + ['message' => $message, 'details' => []];
        }
    }

    $statusMessage = session('status');

    if (is_string($statusMessage) && trim($statusMessage) !== '') {
        $statusType = preg_match('/success|created|updated|deleted|approved|saved|moved/i', $statusMessage)
            ? 'success'
            : 'info';

        $toastItems[] = [
            'type' => $statusType,
            'title' => $statusType === 'success' ? 'Success' : 'Notice',
            'message' => $statusMessage,
            'details' => [],
        ];
    }

    $authReturnPath = $returnPath ?? null;

    if (! session()->has('auth_notice') && is_string($authReturnPath) && trim($authReturnPath) !== '') {
        $toastItems[] = [
            'type' => 'info',
            'title' => 'Login required',
            'message' => 'Authentication is required before continuing.',
            'details' => ['Return URL: ' . $authReturnPath],
        ];
    }

    $viewErrors = $errors ?? null;

    if ($viewErrors && $viewErrors->any()) {
        $validationErrors = collect($viewErrors->all())->unique()->values();
        $visibleErrors = $validationErrors->take(4)->all();
        $remainingErrors = max(0, $validationErrors->count() - count($visibleErrors));

        if ($remainingErrors > 0) {
            $visibleErrors[] = $remainingErrors . ' more issue' . ($remainingErrors === 1 ? '' : 's') . ' found.';
        }

        $toastItems[] = [
            'type' => 'danger',
            'title' => 'Check the form',
            'message' => 'Please review the highlighted fields and try again.',
            'details' => $visibleErrors,
        ];
    }
@endphp

<div class="toast-stack position-fixed top-0 end-0 p-3" data-toast-container aria-live="polite" aria-atomic="true">
    @foreach ($toastItems as $toast)
        <div class="toast app-toast app-toast-{{ $toast['type'] }}" role="alert" aria-live="{{ $toast['type'] === 'danger' ? 'assertive' : 'polite' }}" aria-atomic="true" data-bs-delay="{{ $toast['type'] === 'danger' ? 9000 : 5500 }}">
            <div class="toast-header">
                <span class="app-toast-icon" aria-hidden="true">
                    <i class="fa {{ [
                        'success' => 'fa-check-circle',
                        'danger' => 'fa-times-circle',
                        'warning' => 'fa-exclamation-triangle',
                        'info' => 'fa-info-circle',
                    ][$toast['type']] ?? 'fa-info-circle' }}"></i>
                </span>
                <strong class="me-auto">{{ $toast['title'] }}</strong>
                <small>Now</small>
                <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <div>{{ $toast['message'] }}</div>
                @if (! empty($toast['details']))
                    <ul class="app-toast-list">
                        @foreach ($toast['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endforeach
</div>

<script>
    (function () {
        var container = document.querySelector('[data-toast-container]');

        if (! container) {
            return;
        }

        var icons = {
            success: 'fa-check-circle',
            danger: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        var titles = {
            success: 'Success',
            danger: 'Error',
            warning: 'Warning',
            info: 'Notice'
        };

        function normalizeType(type) {
            return Object.prototype.hasOwnProperty.call(icons, type) ? type : 'info';
        }

        function createToast(type, title, message, delay) {
            if (typeof bootstrap === 'undefined' || ! bootstrap.Toast) {
                return;
            }

            type = normalizeType(type);

            var toast = document.createElement('div');
            toast.className = 'toast app-toast app-toast-' + type;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', type === 'danger' ? 'assertive' : 'polite');
            toast.setAttribute('aria-atomic', 'true');
            toast.setAttribute('data-bs-delay', String(delay || 4200));

            var header = document.createElement('div');
            header.className = 'toast-header';

            var iconWrap = document.createElement('span');
            iconWrap.className = 'app-toast-icon';
            iconWrap.setAttribute('aria-hidden', 'true');

            var icon = document.createElement('i');
            icon.className = 'fa ' + icons[type];
            iconWrap.appendChild(icon);

            var heading = document.createElement('strong');
            heading.className = 'me-auto';
            heading.textContent = title || titles[type];

            var time = document.createElement('small');
            time.textContent = 'Now';

            var close = document.createElement('button');
            close.type = 'button';
            close.className = 'btn-close ms-2 mb-1';
            close.setAttribute('data-bs-dismiss', 'toast');
            close.setAttribute('aria-label', 'Close');

            header.appendChild(iconWrap);
            header.appendChild(heading);
            header.appendChild(time);
            header.appendChild(close);

            var body = document.createElement('div');
            body.className = 'toast-body';
            body.textContent = message || 'Working on your request...';

            toast.appendChild(header);
            toast.appendChild(body);
            toast.addEventListener('hidden.bs.toast', function () {
                toast.remove();
            });

            container.appendChild(toast);
            new bootstrap.Toast(toast).show();
        }

        window.startupToast = createToast;

        function pendingMessageFor(form) {
            if (form.dataset.toastPending) {
                return form.dataset.toastPending;
            }

            var submitter = document.activeElement;

            if (submitter && form.contains(submitter)) {
                var label = submitter.textContent.trim();

                if (label) {
                    return label + '...';
                }
            }

            return 'Processing your request...';
        }

        function bootToasts() {
            if (typeof bootstrap === 'undefined' || ! bootstrap.Toast) {
                return;
            }

            container.querySelectorAll('.toast').forEach(function (toast) {
                toast.addEventListener('hidden.bs.toast', function () {
                    toast.remove();
                });

                new bootstrap.Toast(toast).show();
            });

            var validationToastVisible = false;

            document.addEventListener('invalid', function (event) {
                var control = event.target;

                if (! control.closest || ! control.closest('form') || validationToastVisible) {
                    return;
                }

                validationToastVisible = true;
                createToast('danger', 'Check the form', control.validationMessage || 'Please complete the highlighted fields.', 6500);

                window.setTimeout(function () {
                    validationToastVisible = false;
                }, 1200);
            }, true);

            document.addEventListener('submit', function (event) {
                var form = event.target;

                if (! form || form.tagName !== 'FORM' || form.dataset.toast === 'off') {
                    return;
                }

                window.setTimeout(function () {
                    if (! event.defaultPrevented) {
                        createToast('info', 'Working', pendingMessageFor(form), 2600);
                    }
                }, 0);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootToasts);
        } else {
            bootToasts();
        }
    })();
</script>

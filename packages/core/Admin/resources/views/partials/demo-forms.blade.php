{{-- NOTE: Formularios de presentación; no representan contratos de BD ni envían información. --}}
@foreach ([
        'Usuario' => [
            'title' => 'Datos del usuario',
            'fields' => [
                'name' => ['label' => 'Nombre', 'type' => 'text', 'placeholder' => 'Nombre de ejemplo', 'required' => true],
                'email' => ['label' => 'Correo electrónico', 'type' => 'email', 'placeholder' => 'usuario@example.com', 'required' => true],
                'state' => ['label' => 'Estado', 'type' => 'select', 'options' => ['Activo', 'Inactivo']],
                'notes' => ['label' => 'Notas', 'type' => 'textarea', 'placeholder' => 'Notas de demostración'],
            ],
            'checks' => ['verified' => 'Correo verificado'],
        ],
        'Configuraciones' => [
            'title' => 'Preferencias generales',
            'fields' => [
                'name' => ['label' => 'Nombre de la plataforma', 'type' => 'text', 'placeholder' => 'UNGOLMAS', 'required' => true],
                'language' => ['label' => 'Idioma', 'type' => 'select', 'options' => ['Español', 'Inglés']],
                'timezone' => ['label' => 'Zona horaria', 'type' => 'select', 'options' => ['America/Lima', 'UTC']],
            ],
            'switches' => ['notifications' => 'Notificaciones', 'maintenance' => 'Modo mantenimiento'],
        ],
        'Juego' => [
            'title' => 'Configuración del juego',
            'fields' => [
                'name' => ['label' => 'Nombre del juego', 'type' => 'text', 'placeholder' => 'Juego de ejemplo', 'required' => true],
                'description' => ['label' => 'Descripción', 'type' => 'textarea', 'placeholder' => 'Describe la dinámica del juego'],
                'state' => ['label' => 'Estado', 'type' => 'select', 'options' => ['Borrador', 'Publicado', 'Archivado']],
            ],
            'radios' => ['label' => 'Participación', 'options' => ['Individual', 'Por equipos']],
            'switches' => ['enabled' => 'Habilitar juego'],
        ],
        'Integraciones' => [
            'title' => 'Configuración del servicio',
            'fields' => [
                'service' => ['label' => 'Servicio', 'type' => 'select', 'options' => ['Noticias', 'Tienda', 'Avatares', 'Ranking', 'Publicidad']],
                'name' => ['label' => 'Nombre de la conexión', 'type' => 'text', 'placeholder' => 'Conexión de ejemplo', 'required' => true],
                'url' => ['label' => 'URL del servicio', 'type' => 'url', 'placeholder' => 'https://example.com', 'help' => 'Solo demostración. No se realizará ninguna conexión.'],
                'environment' => ['label' => 'Entorno', 'type' => 'select', 'options' => ['Demostración'], 'disabled' => true, 'help' => 'Los entornos reales todavía no están disponibles.'],
            ],
            'switches' => ['sync' => 'Sincronización automática'],
        ],
        'Account settings' => [
            'title' => 'Preferencias del perfil',
            'fields' => [
                'name' => ['label' => 'Nombre visible', 'type' => 'text', 'placeholder' => 'Administrador', 'required' => true],
                'email' => ['label' => 'Correo electrónico', 'type' => 'email', 'placeholder' => 'admin@example.com', 'required' => true],
                'language' => ['label' => 'Idioma', 'type' => 'select', 'options' => ['Español', 'Inglés']],
            ],
            'switches' => ['notifications' => 'Recibir notificaciones'],
        ],
    ] as $page => $form)
    <form data-demo-form="{{ $page }}" class="admin-demo-form hidden" autocomplete="off"
        aria-labelledby="demo-form-title-{{ $loop->index }}">
        <div class="admin-form-heading">
            <h2 id="demo-form-title-{{ $loop->index }}">{{ $form['title'] }}</h2>
            <p>Vista previa · Los cambios no se guardan.</p>
        </div>

        <div class="admin-form-fields">
            @foreach ($form['fields'] as $key => $field)
                <div class="admin-form-field">
                    <label for="demo-{{ $loop->parent->index }}-{{ $key }}">
                        {{ $field['label'] }}
                        @if ($field['required'] ?? false)
                            <span aria-hidden="true">*</span>
                        @endif
                    </label>
                    @if ($field['type'] === 'select')
                        <div class="admin-form-select">
                            <select id="demo-{{ $loop->parent->index }}-{{ $key }}" name="{{ $key }}"
                                @disabled($field['disabled'] ?? false)
                                @if (isset($field['help'])) aria-describedby="demo-help-{{ $loop->parent->index }}-{{ $key }}" @endif>
                                @foreach ($field['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m7 10 5 5 5-5" />
                            </svg>
                        </div>
                    @elseif ($field['type'] === 'textarea')
                        <textarea id="demo-{{ $loop->parent->index }}-{{ $key }}" name="{{ $key }}" rows="3"
                            placeholder="{{ $field['placeholder'] }}" maxlength="1000"></textarea>
                    @else
                        <input id="demo-{{ $loop->parent->index }}-{{ $key }}" name="{{ $key }}"
                            type="{{ $field['type'] }}" placeholder="{{ $field['placeholder'] }}" maxlength="255"
                            @required($field['required'] ?? false)
                            @if (isset($field['help'])) aria-describedby="demo-help-{{ $loop->parent->index }}-{{ $key }}" @endif>
                    @endif
                    @if (isset($field['help']))
                        <p id="demo-help-{{ $loop->parent->index }}-{{ $key }}" class="admin-form-help">
                            {{ $field['help'] }}</p>
                    @endif
                </div>
            @endforeach

            @if (isset($form['radios']))
                <fieldset class="admin-form-group">
                    <legend>{{ $form['radios']['label'] }}</legend>
                    <div class="admin-form-choices">
                        @foreach ($form['radios']['options'] as $option)
                            <label class="admin-form-choice">
                                <input type="radio" name="participation" value="{{ $option }}"
                                    @checked($loop->first)>
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            @foreach ($form['checks'] ?? [] as $key => $label)
                <label class="admin-form-choice">
                    <input type="checkbox" name="{{ $key }}">
                    <span>{{ $label }}</span>
                </label>
            @endforeach

            @if (isset($form['switches']))
                <fieldset class="admin-form-group">
                    <legend>Opciones</legend>
                    <div class="admin-form-toggles">
                        @foreach ($form['switches'] as $key => $label)
                            <label class="admin-form-switch">
                                <span>{{ $label }}</span>
                                <input type="checkbox" role="switch" name="{{ $key }}">
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif
        </div>

        <div class="admin-form-footer">
            <p data-demo-form-status role="status" aria-live="polite"></p>
            <div class="admin-form-actions">
                <button type="reset" class="admin-form-button">Restablecer</button>
                <button type="button" data-demo-form-preview class="admin-form-button admin-form-primary">Probar
                    formulario</button>
            </div>
        </div>
    </form>
@endforeach

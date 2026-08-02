{{--
    Reusable iOS-style toggle field, replacing a plain <select> for
    boolean-ish fields (status, is_featured, etc). Submits the exact same
    name/value (1/0) the backend already validates.

    Params:
      name        (string)  input name, e.g. "status"
      label       (string)  bold row label, e.g. "Estado"
      description (string)  optional muted helper line
      checked     (bool)    initial on/off state
      onchange    (string)  optional inline JS, dispatched via the hidden
                             input's native "change" event (see toggle.js)
--}}
<div class="au-field au-toggle-row">
    <div>
        <div class="au-toggle-row-label">{{ $label }}</div>
        @if (!empty($description))
            <div class="au-toggle-row-desc">{{ $description }}</div>
        @endif
    </div>
    <label class="au-toggle {{ !empty($checked) ? 'is-on' : '' }}" data-au-toggle>
        <input type="checkbox" class="au-toggle-input" hidden {{ !empty($checked) ? 'checked' : '' }}>
        <span class="au-toggle-track"><span class="au-toggle-knob"></span></span>
    </label>
    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ !empty($checked) ? 1 : 0 }}"
        data-au-toggle-value
        @if (!empty($onchange)) onchange="{{ $onchange }}" @endif
    >
</div>

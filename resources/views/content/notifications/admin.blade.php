@extends('layouts.layoutMaster')

@section('title', 'Admin - Notificações')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/forms-selects.js'
])
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
  <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
    <i class="bx bx-check-circle me-1"></i> Sucesso!
  </h6>
  <p class="mb-0">{!! session('success') !!}</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible" role="alert">
  <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
    <i class="bx bx-x-circle me-1"></i> Erro!
  </h6>
  <ul class="mb-0">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="mb-0 text-primary">
    <i class="bx bx-bell"></i> Enviar Notificações
  </h1>
  <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
    <i class="bx bx-arrow-back"></i> Voltar
  </a>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">Nova Notificação / Lembrete</h5>
  </div>
  <div class="card-body">
    <form id="notificationForm" action="{{ route('notifications.send') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-bold">Título *</label>
          <input type="text" name="title" class="form-control" maxlength="120" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">Tipo *</label>
          <select name="type" class="form-select select2" data-placeholder="Selecione o tipo" required>
            <option value=""></option>
            <option value="info">Informativo</option>
            <option value="success">Sucesso</option>
            <option value="warning" selected>Aviso</option>
            <option value="danger">Erro</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-bold">Mensagem *</label>
          <textarea name="message" class="form-control" rows="4" maxlength="5000" required></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">Link (opcional)</label>
          <input type="url" name="link" class="form-control" placeholder="https://...">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">Imagem (opcional)</label>
          <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">Destino *</label>
          <select id="targetCombined" class="form-select select2" data-placeholder="Selecione o destino" required>
            <option value=""></option>
            <option value="all" selected>Todos os usuários</option>
            <optgroup label="Cargo">
              @foreach($roles as $role)
              <option value="role:{{ $role }}">{{ ucfirst($role) }}</option>
              @endforeach
            </optgroup>
            <optgroup label="Usuários">
              @foreach($users as $u)
              <option value="user:{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) - {{ $u->role }}</option>
              @endforeach
            </optgroup>
          </select>
          <input type="hidden" name="target" id="hiddenTarget" value="all">
          <input type="hidden" name="role" id="hiddenRole" value="">
          <input type="hidden" name="user_id" id="hiddenUserId" value="">
        </div>
        <div class="col-md-6">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" id="requireConfirm" name="require_confirm" value="1">
            <label class="form-check-label" for="requireConfirm">
              Requer confirmação do usuário
            </label>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">Agendar (opcional)</label>
          <input type="datetime-local" name="scheduled_at" class="form-control">
          <small class="text-muted">Se preenchido, será enviado como lembrete na data/hora informada.</small>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bx bx-send"></i> Enviar
        </button>
        <button type="button" class="btn btn-outline-secondary" id="previewBtn">
          <i class="bx bx-show"></i> Preview
        </button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Lembretes Agendados</h5>
  </div>
  <div class="card-body">
    @if(count($scheduled) === 0)
    <p class="text-muted">Nenhum lembrete agendado.</p>
    @else
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Título</th>
            <th>Destino</th>
            <th>Agendado para</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach($scheduled as $s)
          <tr>
            <td>
              <strong>{{ $s['title'] }}</strong>
              <div class="small text-muted">{{ $s['message'] }}</div>
            </td>
            <td>
              @if($s['target'] === 'all')
              Todos
              @elseif($s['target'] === 'role')
              Cargo: <span class="badge bg-label-primary">{{ $s['role'] }}</span>
              @else
              Usuário ID: {{ $s['user_id'] }}
              @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($s['scheduled_at'])->format('d/m/Y H:i') }}</td>
            <td>
              @if(!empty($s['sent_at']))
              <span class="badge bg-success">Enviado</span>
              @else
              <span class="badge bg-warning">Pendente</span>
              @endif
            </td>
            <td>
              @if(empty($s['sent_at']))
              <form action="{{ route('notifications.scheduled.cancel') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="id" value="{{ $s['id'] }}">
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancelar este lembrete?')">
                  <i class="bx bx-x-circle"></i> Cancelar
                </button>
              </form>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const combined = document.getElementById('targetCombined');
    const hiddenTarget = document.getElementById('hiddenTarget');
    const hiddenRole = document.getElementById('hiddenRole');
    const hiddenUserId = document.getElementById('hiddenUserId');
    const form = document.getElementById('notificationForm');
    const previewBtn = document.getElementById('previewBtn');

    function applySelection(val) {
      hiddenTarget.value = '';
      hiddenRole.value = '';
      hiddenUserId.value = '';
      if (!val) return;
      if (val === 'all') {
        hiddenTarget.value = 'all';
        return;
      }
      const [kind, payload] = val.split(':');
      if (kind === 'role') {
        hiddenTarget.value = 'role';
        hiddenRole.value = payload || '';
      } else if (kind === 'user') {
        hiddenTarget.value = 'user';
        hiddenUserId.value = payload || '';
      }
    }

    combined.addEventListener('change', function() {
      applySelection(this.value);
    });

    combined.value = 'all';
    applySelection('all');

    form.addEventListener('submit', function(e) {
      if (!hiddenTarget.value) {
        e.preventDefault();
        alert('Selecione o destino da notificação.');
        return;
      }
      const messageEl = form.querySelector('textarea[name="message"]');
      if (messageEl && messageEl.value.length > 5000) {
        e.preventDefault();
        alert('A mensagem é muito longa (máximo 5000 caracteres).');
        return;
      }
    });

    previewBtn.addEventListener('click', function() {
      const title = form.querySelector('input[name="title"]').value || 'Notificação';
      const message = form.querySelector('textarea[name="message"]').value || '';
      const type = form.querySelector('select[name="type"]').value || 'info';
      const link = form.querySelector('input[name="link"]').value || '';
      const imageInput = form.querySelector('input[name="image"]');
      const require = document.getElementById('requireConfirm').checked;
      const modalEl = document.getElementById('notificationModal');
      if (!modalEl) return;
      document.getElementById('notificationModalTitle').textContent = title;
      document.getElementById('notificationModalMessage').textContent = message;
      const iconEl = document.getElementById('notificationModalIcon');
      const badgeEl = document.getElementById('notificationModalBadge');
      let iconClass = 'bx-info-circle';
      let badgeClass = 'bg-label-info';
      if (type === 'success') {
        iconClass = 'bx-check-circle';
        badgeClass = 'bg-label-success';
      }
      if (type === 'warning') {
        iconClass = 'bx-error';
        badgeClass = 'bg-label-warning';
      }
      if (type === 'danger') {
        iconClass = 'bx-x-circle';
        badgeClass = 'bg-label-danger';
      }
      iconEl.className = 'bx ' + iconClass;
      badgeEl.className = 'avatar-initial rounded-circle ' + badgeClass;
      const linkEl = document.getElementById('notificationModalLink');
      if (link) {
        linkEl.href = link;
        linkEl.style.display = 'inline-block';
      } else {
        linkEl.style.display = 'none';
      }
      const modalImage = document.getElementById('notificationModalImage');
      if (modalImage) {
        if (imageInput && imageInput.files && imageInput.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            modalImage.src = e.target.result;
            modalImage.style.display = 'block';
          };
          reader.readAsDataURL(imageInput.files[0]);
        } else {
          modalImage.removeAttribute('src');
          modalImage.style.display = 'none';
        }
      }
      const confirmArea = document.getElementById('notificationConfirmArea');
      confirmArea.style.display = require ? 'block' : 'none';
      document.getElementById('notificationConfirmBtn').onclick = null;
      document.getElementById('notificationDeclineBtn').onclick = null;
      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();
    });
  });
</script>
@endpush

@endsection
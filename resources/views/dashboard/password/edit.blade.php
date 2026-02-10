<form method="POST" action="{{ route('password.update') }}">
@csrf

<div class="mb-3">
    <label>Password Lama</label>
    <input type="password" name="password_lama" class="form-control">
</div>

<div class="mb-3">
    <label>Password Baru</label>
    <input type="password" name="password_baru" class="form-control">
</div>

<div class="mb-3">
    <label>Konfirmasi Password</label>
    <input type="password" name="password_baru_confirmation" class="form-control">
</div>

<button class="btn btn-success">Simpan</button>
</form>

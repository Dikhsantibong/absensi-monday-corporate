@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 flex flex-col justify-center">
    <div class="max-w-xl mx-auto bg-white shadow rounded-2xl p-8">

        <h2 class="text-2xl font-bold text-center mb-6">
            Form Absensi
        </h2>

        <div id="error-message"
             class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <span></span>
        </div>

        <form id="attendance-form" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="signature" id="signature-data">

            <div>
                <label>Nama</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label>Jabatan</label>
                <input type="text" name="position" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label>Divisi</label>
                <input type="text" name="division" required class="w-full border rounded px-3 py-2">
            </div>

            <!-- SIGNATURE -->
            <div>
                <label>Tanda Tangan</label>
                <div class="border rounded p-2">
                    <canvas id="signature-pad" class="w-full h-48 border rounded"></canvas>

                    <div class="flex justify-between mt-2">
                        <button type="button" id="clear" class="text-red-600">Hapus</button>
                        <button type="button" id="undo" class="text-blue-600">Undo</button>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Kirim Absensi
            </button>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)'
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    document.getElementById('clear').onclick = () => signaturePad.clear();

    document.getElementById('undo').onclick = () => {
        const data = signaturePad.toData();
        if (data.length) {
            data.pop();
            signaturePad.fromData(data);
        }
    };

    document.getElementById('attendance-form').addEventListener('submit', async e => {
        e.preventDefault();

        if (signaturePad.isEmpty()) {
            showError('Tanda tangan wajib diisi');
            return;
        }

        document.getElementById('signature-data').value =
            signaturePad.toDataURL('image/png');

        const formData = new FormData(e.target);

        const response = await fetch('{{ route("scan.submit", ["token" => $token]) }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert('Absensi berhasil disimpan');
            setTimeout(() => {
                window.location.href = '{{ url("/") }}';
            }, 1000);
        } else {
            // Handle validation errors
            let errorMessage = 'Terjadi kesalahan saat menyimpan absensi';
            
            if (result.message) {
                errorMessage = result.message;
            } else if (result.errors) {
                // Laravel validation errors
                const errorMessages = Object.values(result.errors).flat();
                errorMessage = errorMessages.join(', ');
            } else if (result.error_type === 'invalid_token') {
                errorMessage = result.message || 'Token tidak valid atau sudah digunakan';
            }
            
            showError(errorMessage);
            console.error('Error response:', result);
        }

    });

    function showError(msg) {
        const box = document.getElementById('error-message');
        box.querySelector('span').innerText = msg;
        box.classList.remove('hidden');
    }
});
</script>
@endsection

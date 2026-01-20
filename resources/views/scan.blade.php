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

        <div id="success-message"
             class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <span></span>
        </div>

        <form id="attendance-form" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            {{-- **TAMBAHKAN HIDDEN INPUT UNIT_SOURCE** --}}
            <input type="hidden" name="unit_source" value="{{ $unitSource }}">
            <input type="hidden" name="signature" id="signature-data">

            <div>
                <label class="block mb-2 font-medium">Nama</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-2 font-medium">Jabatan</label>
                <input type="text" name="position" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-2 font-medium">Divisi</label>
                <input type="text" name="division" required class="w-full border rounded px-3 py-2">
            </div>

            <!-- SIGNATURE -->
            <div>
                <label class="block mb-2 font-medium">Tanda Tangan</label>
                <div class="border rounded p-2">
                    <canvas id="signature-pad" class="w-full h-48 border rounded bg-white"></canvas>

                    <div class="flex justify-between mt-2">
                        <button type="button" id="clear" class="text-red-600 px-4 py-2">Hapus</button>
                        <button type="button" id="undo" class="text-blue-600 px-4 py-2">Undo</button>
                    </div>
                </div>
            </div>

            <button type="submit"
                    id="submit-btn"
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

        // Reset messages
        hideError();
        hideSuccess();

        // Validate signature
        if (signaturePad.isEmpty()) {
            showError('Tanda tangan wajib diisi');
            return;
        }

        // Disable button saat submit
        const submitBtn = document.getElementById('submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengirim...';

        try {
            document.getElementById('signature-data').value = signaturePad.toDataURL('image/png');

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
                showSuccess(result.message || 'Absensi berhasil disimpan');
                
                // Reset form
                e.target.reset();
                signaturePad.clear();
                
                // Redirect setelah 2 detik
                setTimeout(() => {
                    window.location.href = '{{ url("/") }}';
                }, 2000);
            } else {
                // Handle errors
                let errorMessage = 'Terjadi kesalahan saat menyimpan absensi';
                
                if (result.error_type === 'already_attended') {
                    errorMessage = result.message || 'Anda sudah melakukan absensi hari ini';
                } else if (result.error_type === 'token_expired') {
                    errorMessage = result.message || 'Token sudah tidak valid';
                } else if (result.message) {
                    errorMessage = result.message;
                } else if (result.errors) {
                    // Laravel validation errors
                    const errorMessages = Object.values(result.errors).flat();
                    errorMessage = errorMessages.join(', ');
                }
                
                showError(errorMessage);
                console.error('Error response:', result);
            }

        } catch (error) {
            console.error('Network error:', error);
            showError('Terjadi kesalahan jaringan. Silakan coba lagi.');
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    function showError(msg) {
        const box = document.getElementById('error-message');
        box.querySelector('span').textContent = msg;
        box.classList.remove('hidden');
        
        // Scroll to top untuk melihat error
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideError() {
        const box = document.getElementById('error-message');
        box.classList.add('hidden');
    }

    function showSuccess(msg) {
        const box = document.getElementById('success-message');
        box.querySelector('span').textContent = msg;
        box.classList.remove('hidden');
        
        // Scroll to top untuk melihat success
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideSuccess() {
        const box = document.getElementById('success-message');
        box.classList.add('hidden');
    }
});
</script>
@endsection
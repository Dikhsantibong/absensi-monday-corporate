@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 flex flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <div class="relative px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
            <div class="max-w-md mx-auto">

                <h2 class="text-2xl font-bold mb-8 text-center text-gray-800">
                    Form Absensi
                </h2>

                <div id="error-message"
                    class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <span></span>
                </div>

                <form id="attendance-form" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Nama -->
                    <div>
                        <label class="block text-gray-600 mb-1">Nama</label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-[#009BB9]">
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-gray-600 mb-1">Jabatan</label>
                        <input type="text" name="position" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-[#009BB9]">
                    </div>

                    <!-- Divisi -->
                    <div>
                        <label class="block text-gray-600 mb-1">Divisi</label>
                        <input type="text" name="division" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-[#009BB9]">
                    </div>

                    <!-- Signature -->
                    <div>
                        <label class="block text-gray-600 mb-2">Tanda Tangan</label>
                        <div class="border rounded-lg p-2">
                            <canvas id="signature-pad"
                                class="border rounded w-full h-48"></canvas>

                            <div class="flex justify-between mt-2">
                                <button type="button" id="clear"
                                    class="text-red-500 hover:text-red-700 text-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                                <button type="button" id="undo"
                                    class="text-blue-500 hover:text-blue-700 text-sm">
                                    <i class="fas fa-undo"></i> Undo
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="signature" id="signature-data">
                    </div>

                    <button type="submit"
                        class="w-full bg-[#0A749B] text-white py-2 rounded-lg hover:bg-[#009BB9] transition">
                        Submit Absensi
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- SignaturePad -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)',
        penColor: 'rgb(0,0,0)'
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }

    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    document.getElementById('clear').onclick = () => {
        signaturePad.clear();
        document.getElementById('signature-data').value = '';
    };

    document.getElementById('undo').onclick = () => {
        const data = signaturePad.toData();
        if (data.length) {
            data.pop();
            signaturePad.fromData(data);
        }
    };

    document.getElementById('attendance-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (signaturePad.isEmpty()) {
            showError('Tanda tangan wajib diisi');
            return;
        }

        document.getElementById('signature-data').value =
            signaturePad.toDataURL('image/png');

        const response = await fetch('{{ route("attendance.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: new FormData(this)
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = '{{ route("attendance.success") }}';
        } else {
            showError(data.message || 'Gagal menyimpan absensi');
        }
    });

    function showError(message) {
        const error = document.getElementById('error-message');
        error.querySelector('span').textContent = message;
        error.classList.remove('hidden');
        setTimeout(() => error.classList.add('hidden'), 5000);
    }
});
</script>
@endsection

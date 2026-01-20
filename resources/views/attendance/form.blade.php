<form id="attendance-form">
    <input type="hidden" name="token" value="{{ $token }}">
    <input name="name" required>
    <input name="position" required>
    <input name="division" required>
    <canvas id="signature-pad"></canvas>
    <input type="hidden" name="signature" id="signature">
    
    <button>Submit</button>
    </form>
    
    <script>
    document.getElementById('attendance-form').onsubmit = async e => {
     e.preventDefault();
     const form = new FormData(e.target);
    
     const res = await fetch('/api/attendance/submit',{
       method:'POST', body:form
     });
    
     if((await res.json()).success){
       location.href='https://monday-upkd.plnnusantarapower.co.id';
     }
    }
    </script>
    
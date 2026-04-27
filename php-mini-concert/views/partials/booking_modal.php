<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            </div>
    </div>
</div>

<script>
    const bookingModal = document.getElementById('bookingModal');
    if (bookingModal) {
        bookingModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const concertId = button.getAttribute('data-concert-id');
            const concertTitle = button.getAttribute('data-concert-title');
            
            bookingModal.querySelector('#modalConcertId').value = concertId;
            bookingModal.querySelector('#modalConcertTitle').textContent = concertTitle;
        });
    }
</script>
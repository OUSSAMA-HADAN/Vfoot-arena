<?php $this->layout('layout', ['title' => 'Rejoindre par code']) ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">

        <div class="text-center mb-4">
            <div style="font-size:3rem">🔐</div>
            <h4 class="fw-bold text-white mt-2 mb-1">Cup privée</h4>
            <p class="text-secondary" style="font-size:.9rem">
                Entrez le code d'invitation partagé par l'organisateur.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 mb-3" style="font-size:.88rem;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;border-radius:8px">
                <i class="bi bi-exclamation-triangle me-1"></i><?= $this->e($error) ?>
            </div>
        <?php endif ?>

        <div class="vf-card p-4">
            <form action="/cups/join-code" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary" style="font-size:.85rem">Code d'invitation</label>
                    <input
                        type="text"
                        name="join_code"
                        class="form-control vf-input text-center"
                        style="letter-spacing:.15em;font-size:1.1rem;font-weight:700;text-transform:uppercase"
                        placeholder="XXXX-XXXX"
                        autocomplete="off"
                        autofocus
                        required
                    >
                </div>
                <button type="submit" class="btn btn-vf w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i>Rejoindre la cup
                </button>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="/cups" class="text-secondary text-decoration-none" style="font-size:.85rem">
                <i class="bi bi-arrow-left me-1"></i>Voir les cups publiques
            </a>
        </div>

    </div>
</div>

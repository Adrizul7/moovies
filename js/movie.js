document.addEventListener('DOMContentLoaded', async () => {
    const id = getMovieId();
    if (!id) return;

    try {
        const res = await fetch(`../api/movie.php?id=${id}`);
        const data = await res.json();

        console.log("Datos del servidor:", data);

        let peli = null;
        if (data.movie) {
            peli = data.movie;
        } else if (data.id || data.title) {
            peli = data;
        }

        const comentarios = data.comments ? data.comments : [];

        if (!peli || (!peli.title && !peli.id)) {
            alert("No se encontró información de la película en la base de datos.");
            return;
        }

        renderMovie(peli);
        renderComments(comentarios);
    } catch (error) {
        console.error("Error cargando la película:", error);
    }
});

function getMovieId() {
    return new URLSearchParams(window.location.search).get('id');
}

function normalizarImagenTMDB(url) {
    if (!url || url.trim() === '') return '';
    return url.replace(/image\.tmdb\.org\/t\/p\/[^/]+\//, 'image.tmdb.org/t/p/w500/');
}

function renderMovie(m) {
    document.getElementById('modalTitle').textContent = m.title || 'Sin título';
    document.getElementById('modalGenre').textContent = m.genres || 'General';
    
    const sinopsisTexto = m.summary || m.description || 'Sin descripción disponible.';
    const sinopsisElem = document.getElementById('modalSynopsis');
    if (sinopsisElem) {
        sinopsisElem.value = sinopsisTexto;
    }

    const imgElem = document.getElementById('modalImg');
    const urlNormalizada = normalizarImagenTMDB(m.image);
    if (urlNormalizada) {
        imgElem.src = urlNormalizada;
        imgElem.onerror = null;
    } else {
        imgElem.style.display = 'none'; // Si no hay imagen, oculta el elemento
    }

    const trailerBtn = document.getElementById('modalTrailerBtn');
    if (m.trailer) {
        trailerBtn.href = m.trailer;
        trailerBtn.style.display = 'inline-flex';
    } else {
        trailerBtn.style.display = 'none';
    }

    document.getElementById('modalDirector').textContent = m.directors || '—';
    document.getElementById('modalActores').textContent = m.actors || '—';
    document.getElementById('modalCompositor').textContent = m.composers || '—';
    document.getElementById('modalGuionistas').textContent = m.writers || '—';
}

function renderComments(comments) {
    const container = document.getElementById('commentsList');
    if (!container) return;
    container.innerHTML = '';

    if (!comments || comments.length === 0) {
        container.innerHTML = '<p>No hay comentarios aún.</p>';
        return;
    }

    comments.forEach(c => {
        const div = document.createElement('div');
        div.className = 'comment-item';
        div.innerHTML = `
            <strong>${c.username || 'Anónimo'}</strong>
            <p>${c.comment || c.content || 'Sin texto'}</p>
            <p>${'🥛'.repeat(c.rating || 0)}</p>
            <div class="comment-actions">
                <button onclick="vote(${c.id}, 1)">👍 ${c.likes || 0}</button>
                <button onclick="vote(${c.id}, -1)">👎 ${c.dislikes || 0}</button>
            </div>
        `;
        container.appendChild(div);
    });
}

let currentRating = 0;
function setRating(n) {
    currentRating = n;
    document.querySelectorAll('#milkRatingRow .glass').forEach((g, i) => {
        g.classList.toggle('active', i < n);
    });
}
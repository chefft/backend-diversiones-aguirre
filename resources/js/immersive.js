import {
    AmbientLight,
    BackSide,
    Box3,
    Color,
    DirectionalLight,
    Euler,
    Group,
    HemisphereLight,
    MathUtils,
    Mesh,
    MeshBasicMaterial,
    MeshStandardMaterial,
    PerspectiveCamera,
    PlaneGeometry,
    Quaternion,
    Scene,
    SphereGeometry,
    SRGBColorSpace,
    StereoCamera,
    TextureLoader,
    TorusKnotGeometry,
    Vector3,
    WebGLRenderer,
} from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';

const API = {
    games: '/api/games',
    galleries: '/api/gallery-360',
    calendar: '/api/calendario',
    reservations: '/api/reservations',
};

export function initImmersiveApp() {
    const mount = document.getElementById('immersive-app');
    if (!mount) {
        return;
    }

    mount.innerHTML = template();

    const elements = bindElements(mount);
    const modelViewer = createModelViewer(elements.modelView);
    const panoramaViewer = createPanoramaViewer(elements.panoramaView);

    const state = {
        games: [],
        galleries: [],
        selectedGame: null,
        selectedGallery: null,
        calendar: [],
        selectedSlotKey: null,
        activeTab: 'model',
        cardboardMode: false,
        deviceGyroEnabled: false,
    };

    setInitialDates(elements);
    wireEvents({
        elements,
        state,
        modelViewer,
        panoramaViewer,
    });

    loadInitialData({
        elements,
        state,
        modelViewer,
        panoramaViewer,
    });
}

async function loadInitialData({ elements, state, modelViewer, panoramaViewer }) {
    setBookingStatus(elements, 'info', 'Cargando catalogo inmersivo...');

    await Promise.all([
        loadGames({ elements, state, modelViewer }),
        loadGalleries({ elements, state, panoramaViewer }),
    ]);

    await refreshCalendar({ elements, state });
    updateViewerOverlay({ elements, state });
}

function wireEvents({ elements, state, modelViewer, panoramaViewer }) {
    elements.gamesList.addEventListener('click', async (event) => {
        const card = event.target.closest('[data-game-id]');
        if (!card) {
            return;
        }

        const gameId = Number(card.dataset.gameId);
        const game = state.games.find((item) => item.id === gameId);
        if (!game) {
            return;
        }

        state.selectedGame = game;
        state.selectedSlotKey = null;
        renderGames(elements, state);
        updateViewerOverlay({ elements, state });
        modelViewer.loadModel(game.model_3d_url);
        updateSelectedGameLabel(elements, game);
        await refreshCalendar({ elements, state });
    });

    elements.scenesList.addEventListener('click', (event) => {
        const card = event.target.closest('[data-scene-id]');
        if (!card) {
            return;
        }

        const galleryId = Number(card.dataset.sceneId);
        const gallery = state.galleries.find((item) => item.id === galleryId);
        if (!gallery) {
            return;
        }

        state.selectedGallery = gallery;
        renderGalleries(elements, state);
        panoramaViewer.loadPanorama(gallery.image_url);
        updateViewerOverlay({ elements, state });
    });

    elements.tabModel.addEventListener('click', () => {
        switchViewerTab({
            elements,
            state,
            nextTab: 'model',
        });
    });

    elements.tabPanorama.addEventListener('click', () => {
        switchViewerTab({
            elements,
            state,
            nextTab: 'panorama',
        });
    });

    elements.cardboardButton.addEventListener('click', async () => {
        if (state.activeTab !== 'panorama') {
            switchViewerTab({
                elements,
                state,
                nextTab: 'panorama',
            });
        }

        const enable = !state.cardboardMode;
        const gyroGranted = enable ? await requestGyroPermission() : false;
        const gyroMode = enable && gyroGranted;

        state.cardboardMode = enable;
        state.deviceGyroEnabled = gyroMode;

        panoramaViewer.setCardboardMode(enable, gyroMode);
        updateCardboardButton(elements, state);
        updateViewerOverlay({ elements, state });

        if (enable) {
            await requestFullscreen(elements.panoramaView);
            lockLandscape();
        } else {
            exitFullscreenSafe();
            unlockLandscape();
        }
    });

    elements.refreshCalendar.addEventListener('click', async () => {
        await refreshCalendar({ elements, state });
    });

    elements.calendarGrid.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-slot-key]');
        if (!button || button.disabled) {
            return;
        }

        const slotKey = button.dataset.slotKey;
        const date = button.dataset.date;
        const start = button.dataset.start;
        const end = button.dataset.end;

        state.selectedSlotKey = slotKey;
        renderCalendar(elements, state);

        elements.bookingStart.value = `${date}T${start}`;
        elements.bookingEnd.value = `${date}T${end}`;
        setBookingStatus(elements, 'info', `Bloque seleccionado: ${date} ${start} - ${end}`);
    });

    elements.bookingForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!state.selectedGame) {
            setBookingStatus(elements, 'error', 'Selecciona un juego para reservar.');
            return;
        }

        if (!elements.bookingStart.value || !elements.bookingEnd.value) {
            setBookingStatus(elements, 'error', 'Selecciona fecha y hora de inicio y fin.');
            return;
        }

        const payload = {
            game_id: state.selectedGame.id,
            start_date: toServerDateTime(elements.bookingStart.value),
            end_date: toServerDateTime(elements.bookingEnd.value),
        };

        elements.bookNow.disabled = true;
        elements.bookNow.textContent = 'Reservando...';

        try {
            await requestJson(API.reservations, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            setBookingStatus(elements, 'ok', 'Reserva creada. Actualizando calendario...');
            state.selectedSlotKey = null;
            await refreshCalendar({ elements, state });
        } catch (error) {
            setBookingStatus(elements, 'error', normalizeApiError(error));
        } finally {
            elements.bookNow.disabled = false;
            elements.bookNow.textContent = 'Reservar ahora';
        }
    });

    window.addEventListener('resize', () => {
        modelViewer.resize();
        panoramaViewer.resize();
    });
}

async function loadGames({ elements, state, modelViewer }) {
    elements.gamesList.innerHTML = '<p class="loading">Cargando juegos...</p>';

    try {
        const games = await requestJson(API.games);
        state.games = Array.isArray(games) ? games : [];

        if (!state.selectedGame && state.games.length > 0) {
            state.selectedGame = state.games[0];
            modelViewer.loadModel(state.selectedGame.model_3d_url);
            updateSelectedGameLabel(elements, state.selectedGame);
        }
    } catch (error) {
        state.games = [];
        elements.gamesList.innerHTML = `<p class="loading">${normalizeApiError(error)}</p>`;
    }

    renderGames(elements, state);
}

async function loadGalleries({ elements, state, panoramaViewer }) {
    elements.scenesList.innerHTML = '<p class="loading">Cargando escenas 360...</p>';

    try {
        const galleries = await requestJson(API.galleries);
        state.galleries = Array.isArray(galleries) ? galleries : [];

        if (!state.selectedGallery && state.galleries.length > 0) {
            state.selectedGallery = state.galleries[0];
            panoramaViewer.loadPanorama(state.selectedGallery.image_url);
        }
    } catch (error) {
        state.galleries = [];
        elements.scenesList.innerHTML = `<p class="loading">${normalizeApiError(error)}</p>`;
    }

    renderGalleries(elements, state);
}

async function refreshCalendar({ elements, state }) {
    const start = elements.calendarStart.value;
    const end = elements.calendarEnd.value;
    const interval = elements.calendarInterval.value;

    if (!start || !end) {
        setBookingStatus(elements, 'error', 'Debes indicar un rango de fechas.');
        return;
    }

    const params = new URLSearchParams({
        inicio: start,
        fin: end,
        intervalo: interval,
    });

    if (state.selectedGame) {
        params.set('game_id', String(state.selectedGame.id));
    }

    elements.calendarGrid.innerHTML = '<p class="loading">Calculando bloques...</p>';

    try {
        const data = await requestJson(`${API.calendar}?${params.toString()}`);
        state.calendar = Array.isArray(data) ? data : [];
        renderCalendar(elements, state);
        setBookingStatus(elements, 'info', 'Calendario actualizado. Elige un bloque disponible.');
    } catch (error) {
        state.calendar = [];
        elements.calendarGrid.innerHTML = `<p class="loading">${normalizeApiError(error)}</p>`;
        setBookingStatus(elements, 'error', normalizeApiError(error));
    }
}

function switchViewerTab({ elements, state, nextTab }) {
    state.activeTab = nextTab;
    const showModel = nextTab === 'model';

    elements.tabModel.classList.toggle('active', showModel);
    elements.tabPanorama.classList.toggle('active', !showModel);
    elements.modelView.classList.toggle('hidden', !showModel);
    elements.panoramaView.classList.toggle('hidden', showModel);
    elements.viewerModeLabel.textContent = showModel ? 'Modo modelo 3D' : 'Modo panorama 360';
    elements.cardboardButton.disabled = showModel;
    updateViewerOverlay({ elements, state });
}

function updateCardboardButton(elements, state) {
    const active = state.cardboardMode;
    elements.cardboardButton.classList.toggle('active', active);
    elements.cardboardButton.textContent = active ? 'Salir de Cardboard' : 'Activar Cardboard';
}

function updateSelectedGameLabel(elements, game) {
    if (!game) {
        elements.selectedGameName.textContent = 'Ningun juego seleccionado';
        return;
    }

    elements.selectedGameName.textContent = `${game.name} | $${Number(game.price_per_hour).toLocaleString('es-MX')}/h`;
}

function renderGames(elements, state) {
    if (state.games.length === 0) {
        elements.gamesList.innerHTML = '<p class="loading">No hay juegos activos todavia.</p>';
        return;
    }

    elements.gamesList.innerHTML = state.games
        .map((game) => {
            const active = state.selectedGame?.id === game.id ? 'active' : '';
            const price = Number(game.price_per_hour).toLocaleString('es-MX');

            return `
                <article class="game-card ${active}" data-game-id="${game.id}">
                    <h3 class="game-title">${escapeHtml(game.name)}</h3>
                    <p class="game-meta">$${price} por hora</p>
                    <p class="game-meta">${escapeHtml(game.description ?? 'Sin descripcion')}</p>
                </article>
            `;
        })
        .join('');
}

function renderGalleries(elements, state) {
    if (state.galleries.length === 0) {
        elements.scenesList.innerHTML = '<p class="loading">No hay escenas 360 activas todavia.</p>';
        return;
    }

    elements.scenesList.innerHTML = state.galleries
        .map((gallery) => {
            const active = state.selectedGallery?.id === gallery.id ? 'active' : '';
            const hotspots = Array.isArray(gallery.hotspots) ? gallery.hotspots.length : 0;

            return `
                <article class="scene-card ${active}" data-scene-id="${gallery.id}">
                    <h3 class="scene-title">${escapeHtml(gallery.title)}</h3>
                    <p class="scene-meta">${hotspots} hotspots configurados</p>
                </article>
            `;
        })
        .join('');
}
function renderCalendar(elements, state) {
    if (state.calendar.length === 0) {
        elements.calendarGrid.innerHTML = '<p class="loading">Sin bloques para el rango seleccionado.</p>';
        return;
    }

    elements.calendarGrid.innerHTML = state.calendar
        .map((day) => {
            const date = new Date(`${day.fecha}T00:00:00`);
            const title = date.toLocaleDateString('es-MX', {
                weekday: 'short',
                month: 'short',
                day: '2-digit',
            });

            const slots = (day.bloques ?? [])
                .map((block) => {
                    const key = `${day.fecha}-${block.inicio}-${block.fin}`;
                    const selected = state.selectedSlotKey === key ? 'selected' : '';
                    const disabled = block.estado !== 'disponible' ? 'disabled' : '';

                    return `
                        <button class="slot ${block.estado} ${selected}"
                                type="button"
                                ${disabled}
                                data-slot-key="${key}"
                                data-date="${day.fecha}"
                                data-start="${block.inicio}"
                                data-end="${block.fin}">
                            <span>${block.inicio}</span>
                            <span>${block.estado}</span>
                        </button>
                    `;
                })
                .join('');

            return `
                <article class="day-column">
                    <h4 class="day-title">${escapeHtml(title)} | ${day.fecha}</h4>
                    <div class="slot-grid">${slots}</div>
                </article>
            `;
        })
        .join('');
}

function setBookingStatus(elements, type, message) {
    elements.bookingStatus.className = `status-chip ${type}`;
    elements.bookingStatus.textContent = message;
}

function updateViewerOverlay({ elements, state }) {
    const gameName = state.selectedGame?.name ?? 'Sin juego seleccionado';
    const sceneName = state.selectedGallery?.title ?? 'Sin escena 360';
    const mode = state.activeTab === 'model' ? 'Modelo 3D' : 'Panorama 360';
    const cardboard = state.cardboardMode
        ? state.deviceGyroEnabled
            ? 'Cardboard + giroscopio'
            : 'Cardboard sin giroscopio'
        : 'Cardboard apagado';

    elements.viewerOverlay.innerHTML = `
        <strong>${escapeHtml(mode)}</strong><br>
        Juego activo: ${escapeHtml(gameName)}<br>
        Escena activa: ${escapeHtml(sceneName)}<br>
        Estado VR: ${escapeHtml(cardboard)}
    `;
}

function createModelViewer(container) {
    const renderer = new WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = SRGBColorSpace;
    container.appendChild(renderer.domElement);

    const scene = new Scene();
    scene.background = new Color('#0d1831');

    const camera = new PerspectiveCamera(50, 1, 0.1, 200);
    camera.position.set(3.8, 2.2, 5.2);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.maxDistance = 18;
    controls.minDistance = 1.8;
    controls.target.set(0, 1, 0);
    controls.update();

    const hemiLight = new HemisphereLight('#8dd8ff', '#191f35', 1.1);
    const dirLight = new DirectionalLight('#ffffff', 1.45);
    dirLight.position.set(4, 7, 2);
    const ambient = new AmbientLight('#85a2ff', 0.35);

    scene.add(hemiLight, dirLight, ambient);

    const floor = new Mesh(
        new PlaneGeometry(26, 26),
        new MeshStandardMaterial({
            color: '#0d2240',
            roughness: 0.9,
            metalness: 0.05,
        }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -1;
    scene.add(floor);

    const root = new Group();
    scene.add(root);

    const fallback = new Mesh(
        new TorusKnotGeometry(0.9, 0.32, 160, 22),
        new MeshStandardMaterial({
            color: '#45ffd2',
            emissive: '#0f3f4b',
            roughness: 0.3,
            metalness: 0.55,
        }),
    );
    fallback.position.y = 0.5;
    root.add(fallback);

    const loader = new GLTFLoader();
    let activeModel = null;
    let pendingToken = 0;

    function loadModel(url) {
        pendingToken += 1;
        const token = pendingToken;

        clearCurrentModel();

        if (!url) {
            fallback.visible = true;
            return;
        }

        loader.load(
            url,
            (gltf) => {
                if (token !== pendingToken) {
                    disposeObject(gltf.scene);
                    return;
                }

                activeModel = gltf.scene;
                fallback.visible = false;

                const bounds = new Box3().setFromObject(activeModel);
                const center = bounds.getCenter(new Vector3());
                const size = bounds.getSize(new Vector3());
                const maxAxis = Math.max(size.x, size.y, size.z) || 1;

                activeModel.position.sub(center);
                const scale = 2.6 / maxAxis;
                activeModel.scale.setScalar(scale);
                root.add(activeModel);

                controls.target.set(0, 0.45, 0);
                camera.position.set(3.5, 2.1, 4.2);
                controls.update();
            },
            undefined,
            () => {
                fallback.visible = true;
            },
        );
    }

    function clearCurrentModel() {
        if (!activeModel) {
            return;
        }

        root.remove(activeModel);
        disposeObject(activeModel);
        activeModel = null;
        fallback.visible = true;
    }

    function resize() {
        const width = container.clientWidth;
        const height = container.clientHeight;
        if (width === 0 || height === 0) {
            return;
        }

        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    }

    function render() {
        resize();

        if (!activeModel) {
            fallback.rotation.x += 0.004;
            fallback.rotation.y += 0.006;
        } else {
            root.rotation.y += 0.003;
        }

        controls.update();
        renderer.render(scene, camera);
        requestAnimationFrame(render);
    }

    render();

    return {
        loadModel,
        resize,
    };
}
function createPanoramaViewer(container) {
    const renderer = new WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = SRGBColorSpace;
    container.appendChild(renderer.domElement);

    const scene = new Scene();
    scene.background = new Color('#070b13');

    const camera = new PerspectiveCamera(75, 1, 0.1, 120);
    camera.position.set(0, 0, 0.01);

    const textureLoader = new TextureLoader();
    const sphere = new Mesh(
        new SphereGeometry(50, 88, 64),
        new MeshBasicMaterial({
            color: '#1b2b45',
            side: BackSide,
        }),
    );
    scene.add(sphere);

    const stereoCamera = new StereoCamera();
    stereoCamera.eyeSep = 0.064;

    let panoramaTexture = null;
    let cardboardMode = false;
    let gyroMode = false;
    let gyroAlpha = 0;
    let gyroBeta = 0;
    let gyroGamma = 0;
    let dragging = false;
    let lastX = 0;
    let lastY = 0;
    let yaw = 0;
    let pitch = 0;
    const zee = new Vector3(0, 0, 1);
    const euler = new Euler();
    const q0 = new Quaternion();
    const q1 = new Quaternion(-Math.sqrt(0.5), 0, 0, Math.sqrt(0.5));

    container.addEventListener('pointerdown', (event) => {
        dragging = true;
        lastX = event.clientX;
        lastY = event.clientY;
    });

    window.addEventListener('pointerup', () => {
        dragging = false;
    });

    window.addEventListener('pointermove', (event) => {
        if (!dragging || gyroMode) {
            return;
        }

        const deltaX = event.clientX - lastX;
        const deltaY = event.clientY - lastY;
        lastX = event.clientX;
        lastY = event.clientY;

        yaw = (yaw - deltaX * 0.13) % 360;
        pitch = MathUtils.clamp(pitch - deltaY * 0.12, -85, 85);
    });

    function applyManualLook() {
        const euler = new Euler(
            MathUtils.degToRad(pitch),
            MathUtils.degToRad(yaw),
            0,
            'YXZ',
        );
        camera.quaternion.setFromEuler(euler);
    }

    function handleDeviceOrientation(event) {
        gyroAlpha = event.alpha ?? gyroAlpha;
        gyroBeta = event.beta ?? gyroBeta;
        gyroGamma = event.gamma ?? gyroGamma;
    }

    function getScreenOrientationAngle() {
        const orientation = window.screen?.orientation?.angle ?? window.orientation ?? 0;
        return Number(orientation) || 0;
    }

    function setObjectQuaternion(quaternion, alpha, beta, gamma, orient) {
        euler.set(beta, alpha, -gamma, 'YXZ');
        quaternion.setFromEuler(euler);
        quaternion.multiply(q1);
        quaternion.multiply(q0.setFromAxisAngle(zee, -orient));
    }

    function applyGyroLook() {
        const alpha = MathUtils.degToRad(gyroAlpha);
        const beta = MathUtils.degToRad(gyroBeta);
        const gamma = MathUtils.degToRad(gyroGamma);
        const orient = MathUtils.degToRad(getScreenOrientationAngle());

        setObjectQuaternion(camera.quaternion, alpha, beta, gamma, orient);
    }

    function setCardboardMode(enabled, useGyro) {
        cardboardMode = enabled;
        gyroMode = enabled && useGyro;

        if (gyroMode) {
            window.addEventListener('deviceorientation', handleDeviceOrientation, true);
        } else {
            window.removeEventListener('deviceorientation', handleDeviceOrientation, true);
        }
    }

    function loadPanorama(url) {
        if (panoramaTexture) {
            panoramaTexture.dispose();
            panoramaTexture = null;
            sphere.material.map = null;
            sphere.material.needsUpdate = true;
        }

        if (!url) {
            sphere.material.color = new Color('#1b2b45');
            sphere.material.needsUpdate = true;
            return;
        }

        textureLoader.load(
            url,
            (texture) => {
                if (panoramaTexture) {
                    panoramaTexture.dispose();
                }

                panoramaTexture = texture;
                panoramaTexture.colorSpace = SRGBColorSpace;
                sphere.material.color = new Color('#ffffff');
                sphere.material.map = panoramaTexture;
                sphere.material.needsUpdate = true;
            },
            undefined,
            () => {
                sphere.material.color = new Color('#1b2b45');
                sphere.material.map = null;
                sphere.material.needsUpdate = true;
            },
        );
    }

    function resize() {
        const width = container.clientWidth;
        const height = container.clientHeight;
        if (width === 0 || height === 0) {
            return;
        }

        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    }

    function render() {
        resize();

        if (gyroMode) {
            applyGyroLook();
        } else {
            applyManualLook();
        }

        const width = container.clientWidth;
        const height = container.clientHeight;

        if (cardboardMode) {
            stereoCamera.aspect = 0.5;
            stereoCamera.update(camera);

            renderer.setScissorTest(true);
            renderer.setViewport(0, 0, width / 2, height);
            renderer.setScissor(0, 0, width / 2, height);
            renderer.render(scene, stereoCamera.cameraL);

            renderer.setViewport(width / 2, 0, width / 2, height);
            renderer.setScissor(width / 2, 0, width / 2, height);
            renderer.render(scene, stereoCamera.cameraR);
            renderer.setScissorTest(false);
        } else {
            renderer.setViewport(0, 0, width, height);
            renderer.render(scene, camera);
        }

        requestAnimationFrame(render);
    }

    render();

    return {
        loadPanorama,
        setCardboardMode,
        resize,
    };
}

function bindElements(root) {
    return {
        gamesList: root.querySelector('#games-list'),
        scenesList: root.querySelector('#scenes-list'),
        modelView: root.querySelector('#model-view'),
        panoramaView: root.querySelector('#panorama-view'),
        viewerOverlay: root.querySelector('#viewer-overlay'),
        viewerModeLabel: root.querySelector('#viewer-mode-label'),
        selectedGameName: root.querySelector('#selected-game-name'),
        tabModel: root.querySelector('#tab-model'),
        tabPanorama: root.querySelector('#tab-panorama'),
        cardboardButton: root.querySelector('#toggle-cardboard'),
        calendarStart: root.querySelector('#calendar-start'),
        calendarEnd: root.querySelector('#calendar-end'),
        calendarInterval: root.querySelector('#calendar-interval'),
        refreshCalendar: root.querySelector('#refresh-calendar'),
        calendarGrid: root.querySelector('#calendar-grid'),
        bookingForm: root.querySelector('#booking-form'),
        bookingStart: root.querySelector('#booking-start'),
        bookingEnd: root.querySelector('#booking-end'),
        bookingStatus: root.querySelector('#booking-status'),
        bookNow: root.querySelector('#book-now'),
    };
}

function setInitialDates(elements) {
    const now = new Date();
    const plusWeek = new Date(now);
    plusWeek.setDate(plusWeek.getDate() + 6);

    elements.calendarStart.value = toDateInputValue(now);
    elements.calendarEnd.value = toDateInputValue(plusWeek);
}

function toDateInputValue(date) {
    const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
}

function toServerDateTime(value) {
    return `${value.replace('T', ' ')}:00`;
}

function template() {
    return `
        <div class="atmosphere aura-a"></div>
        <div class="atmosphere aura-b"></div>
        <div class="atmosphere aura-c"></div>

        <div class="app-shell">
            <header class="hero">
                <p class="eyebrow">Experiencia XR lista para eventos</p>
                <h1 class="hero-title">Diversiones Aguirre Immersive Hub</h1>
                <p class="hero-subtitle">
                    Catalogo de atracciones en 3D, recorrido 360 y calendario de reservas en tiempo real,
                    con modo estereoscopico para visores Cardboard.
                </p>
                <div class="hero-actions">
                    <span class="chip">UI conectada a /api/games</span>
                    <span class="chip">Calendario conectado a /api/calendario</span>
                    <span class="chip">Reserva conectada a /api/reservations</span>
                </div>
            </header>

            <main class="grid-layout">
                <section class="glass-panel">
                    <div class="panel-heading">
                        <h2>Catalogo de juegos</h2>
                        <span class="panel-hint">Selecciona un juego para el visor y la reserva</span>
                    </div>
                    <div id="games-list" class="catalog-list"></div>

                    <div class="panel-heading">
                        <h2>Escenas 360</h2>
                        <span class="panel-hint">Panoramas equirectangulares</span>
                    </div>
                    <div id="scenes-list" class="scene-list"></div>
                </section>

                <section class="glass-panel">
                    <div class="panel-heading">
                        <h2>Visor inmersivo</h2>
                        <span id="viewer-mode-label" class="panel-hint">Modo modelo 3D</span>
                    </div>

                    <div class="viewer-toolbar">
                        <button id="tab-model" class="btn active" type="button">Modelo 3D</button>
                        <button id="tab-panorama" class="btn" type="button">Panorama 360</button>
                        <button id="toggle-cardboard" class="btn warning" type="button">Activar Cardboard</button>
                    </div>

                    <div class="viewer-stage">
                        <div id="model-view" class="viewer-canvas"></div>
                        <div id="panorama-view" class="viewer-canvas hidden"></div>
                        <div id="viewer-overlay" class="viewer-overlay"></div>
                    </div>
                </section>

                <section class="glass-panel">
                    <div class="panel-heading">
                        <h2>Calendario y reserva</h2>
                        <span id="selected-game-name" class="panel-hint">Ningun juego seleccionado</span>
                    </div>

                    <div class="calendar-controls">
                        <div class="field-row">
                            <div class="field">
                                <label for="calendar-start">Inicio</label>
                                <input id="calendar-start" type="date" required>
                            </div>
                            <div class="field">
                                <label for="calendar-end">Fin</label>
                                <input id="calendar-end" type="date" required>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field">
                                <label for="calendar-interval">Intervalo por bloque</label>
                                <select id="calendar-interval">
                                    <option value="30">30 minutos</option>
                                    <option value="60" selected>60 minutos</option>
                                    <option value="90">90 minutos</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Zona horaria</label>
                                <input value="America/Mexico_City" readonly>
                            </div>
                        </div>
                        <button id="refresh-calendar" class="btn primary" type="button">Actualizar calendario</button>
                    </div>

                    <div id="calendar-grid" class="calendar-scroll"></div>

                    <form id="booking-form" class="booking-form">
                        <div id="booking-status" class="status-chip info">Selecciona un bloque disponible para reservar.</div>

                        <div class="field">
                            <label for="booking-start">Inicio de reserva</label>
                            <input id="booking-start" type="datetime-local" required>
                        </div>
                        <div class="field">
                            <label for="booking-end">Fin de reserva</label>
                            <input id="booking-end" type="datetime-local" required>
                        </div>

                        <button id="book-now" class="btn primary" type="submit">Reservar ahora</button>
                    </form>
                </section>
            </main>

            <section class="asset-guide">
                <h3>Guia de assets y VR</h3>
                <p>1) Tus archivos fuente <code>.blend</code>: <code>storage/app/public/models/source/</code></p>
                <p>2) Exporta Blender a <code>.glb</code> y guardalo en <code>storage/app/public/models/</code></p>
                <p>3) Tus panoramas 360 (equirectangular JPG/PNG): <code>storage/app/public/panoramas/</code></p>
                <p>4) En DB usa rutas relativas, por ejemplo <code>models/rueda.glb</code> y <code>panoramas/plaza-central.jpg</code></p>
                <p>5) Ejecuta <code>php artisan storage:link</code> para exponerlos como <code>/storage/...</code></p>
            </section>
        </div>
    `;
}
async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            ...(options.headers ?? {}),
        },
        ...options,
    });

    let payload = null;
    try {
        payload = await response.json();
    } catch {
        payload = null;
    }

    if (!response.ok) {
        throw {
            status: response.status,
            payload,
        };
    }

    return payload;
}

function normalizeApiError(error) {
    if (error?.payload?.message) {
        return error.payload.message;
    }

    if (error?.status) {
        return `Error ${error.status} al consultar el servidor.`;
    }

    return 'No se pudo completar la solicitud.';
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function disposeObject(object) {
    object.traverse((child) => {
        if (!child.isMesh) {
            return;
        }

        if (child.geometry) {
            child.geometry.dispose();
        }

        const material = child.material;
        if (Array.isArray(material)) {
            material.forEach((item) => item?.dispose?.());
        } else {
            material?.dispose?.();
        }
    });
}

async function requestFullscreen(element) {
    if (!element.requestFullscreen || document.fullscreenElement) {
        return;
    }

    try {
        await element.requestFullscreen();
    } catch {
        // ignore
    }
}

async function requestGyroPermission() {
    if (typeof window === 'undefined' || typeof window.DeviceOrientationEvent === 'undefined') {
        return false;
    }

    const requestPermission = window.DeviceOrientationEvent.requestPermission;
    if (typeof requestPermission !== 'function') {
        return true;
    }

    try {
        const result = await requestPermission.call(window.DeviceOrientationEvent);
        return result === 'granted';
    } catch {
        return false;
    }
}

function lockLandscape() {
    if (screen.orientation?.lock) {
        screen.orientation.lock('landscape').catch(() => {
            // ignore
        });
    }
}

function unlockLandscape() {
    if (screen.orientation?.unlock) {
        try {
            screen.orientation.unlock();
        } catch {
            // ignore
        }
    }
}

function exitFullscreenSafe() {
    if (!document.fullscreenElement || !document.exitFullscreen) {
        return;
    }

    document.exitFullscreen().catch(() => {
        // ignore
    });
}

/**
 * ✈️ THREE.JS & GSAP 3D AIRPLANE & HEART SMOKE CONTRAIL ENGINE
 * DongAnh Discovery Hero Banner - Ultra-Smooth Photorealistic Flight Experience
 * Features: High-Def Modern Airbus A350 in Cruising Flight (Landing Gear Retracted)
 * Architecture: 100% Asynchronous Promise/Await Engine (Independent Non-Blocking Parallel Load)
 */
(function () {
    'use strict';

    // Helper: Asynchronously load or wait for any global script/service via Promise
    function awaitGlobalService(globalName, scriptUrl) {
        if (window[globalName] !== undefined) {
            return Promise.resolve(window[globalName]);
        }
        return new Promise((resolve) => {
            const check = () => {
                if (window[globalName] !== undefined) {
                    resolve(window[globalName]);
                    return true;
                }
                return false;
            };

            if (check()) return;

            let script = document.querySelector(`script[src*="${scriptUrl}"]`);
            if (!script && scriptUrl) {
                script = document.createElement('script');
                script.src = scriptUrl;
                script.async = true;
                document.head.appendChild(script);
            }

            const interval = setInterval(() => {
                if (check()) clearInterval(interval);
            }, 16);
        });
    }

    // Helper: Asynchronous Texture Loader with native Promise
    function loadTextureAsync(loader, url) {
        return new Promise((resolve, reject) => {
            loader.load(
                url,
                (texture) => resolve(texture),
                undefined,
                (err) => reject(err)
            );
        });
    }

    async function initAirplaneEngineAsync() {
        const stage = document.getElementById('hero-airplane-3d-stage');
        const canvas = document.getElementById('heroAirplaneCanvas');
        const heroBanner = document.querySelector('.custom-hero-banner');

        if (!stage || !canvas || !heroBanner) return;

        // Check prefers-reduced-motion
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            stage.style.display = 'none';
            return;
        }

        try {
            // 🚀 PARALLEL ASYNC LOADING: Load Three.js and GSAP concurrently without blocking each other!
            const [THREE, gsap] = await Promise.all([
                awaitGlobalService('THREE', '/js/three.min.js'),
                awaitGlobalService('gsap', '/js/gsap.min.js')
            ]);

            // ==========================================
            // 1. SCENE, CAMERA & RENDERER SETUP
            // ==========================================
            let width = stage.clientWidth || heroBanner.clientWidth || window.innerWidth;
            let height = stage.clientHeight || heroBanner.clientHeight || 560;

            const scene = new THREE.Scene();

            const camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 1000);
            camera.position.set(0, 0, 20);

            const renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance',
                stencil: false,
                depth: true
            });

            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            renderer.setClearColor(0x000000, 0); // 100% Transparent background

            // Color Management & Filmic Tone Mapping to prevent overexposure/blown out white
            if (THREE.sRGBEncoding) {
                renderer.outputEncoding = THREE.sRGBEncoding;
            }
            if (THREE.ACESFilmicToneMapping) {
                renderer.toneMapping = THREE.ACESFilmicToneMapping;
                renderer.toneMappingExposure = 1.0;
            }

            // ==========================================
            // 2. BALANCED NATURAL SKY LIGHTING
            // ==========================================
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.90);
            scene.add(ambientLight);

            const sunLight = new THREE.DirectionalLight(0xfff7ed, 0.52);
            sunLight.position.set(10, 14, 10);
            scene.add(sunLight);

            const skyBounceLight = new THREE.DirectionalLight(0xbae6fd, 0.24);
            skyBounceLight.position.set(-8, -6, 4);
            scene.add(skyBounceLight);

            // ==========================================
            // 3. 💖 3D HEART CONTRAIL IN THE SKY
            // ==========================================
            class Heart3DCurve extends THREE.Curve {
                constructor(scale = 0.20) {
                    super();
                    this.scale = scale;
                }
                getPoint(t, optionalTarget = new THREE.Vector3()) {
                    const angle = t * Math.PI * 2;
                    const x = 16 * Math.pow(Math.sin(angle), 3);
                    const y = 13 * Math.cos(angle) - 5 * Math.cos(2 * angle) - 2 * Math.cos(3 * angle) - Math.cos(4 * angle);
                    const z = Math.sin(angle) * 1.5;
                    return optionalTarget.set(
                        x * this.scale,
                        y * this.scale + 0.6,
                        z * this.scale - 1.2
                    );
                }
            }

            const heartCurve = new Heart3DCurve(0.19);
            const heartTubeGeo = new THREE.TubeGeometry(heartCurve, 120, 0.12, 12, true);
            const heartGlowGeo = new THREE.TubeGeometry(heartCurve, 120, 0.24, 12, true);

            const heartCanvas = document.createElement('canvas');
            heartCanvas.width = 256;
            heartCanvas.height = 64;
            const hCtx = heartCanvas.getContext('2d');
            const grad = hCtx.createLinearGradient(0, 0, 256, 0);
            grad.addColorStop(0, 'rgba(255, 255, 255, 0.85)');
            grad.addColorStop(0.3, 'rgba(253, 164, 175, 0.8)');
            grad.addColorStop(0.7, 'rgba(244, 63, 94, 0.75)');
            grad.addColorStop(1, 'rgba(255, 255, 255, 0.85)');
            hCtx.fillStyle = grad;
            hCtx.fillRect(0, 0, 256, 64);

            const heartTexture = new THREE.CanvasTexture(heartCanvas);
            heartTexture.wrapS = THREE.RepeatWrapping;
            heartTexture.wrapT = THREE.RepeatWrapping;
            heartTexture.repeat.set(4, 1);
            if (THREE.sRGBEncoding) heartTexture.encoding = THREE.sRGBEncoding;

            const heartMaterial = new THREE.MeshBasicMaterial({
                map: heartTexture,
                transparent: true,
                opacity: 0.78,
                blending: THREE.NormalBlending,
                depthWrite: false,
            });

            const heartGlowMaterial = new THREE.MeshBasicMaterial({
                color: 0xf43f5e,
                transparent: true,
                opacity: 0.22,
                blending: THREE.NormalBlending,
                depthWrite: false,
            });

            const heartMesh = new THREE.Mesh(heartTubeGeo, heartMaterial);
            const heartGlowMesh = new THREE.Mesh(heartGlowGeo, heartGlowMaterial);
            const heartGroup = new THREE.Group();
            heartGroup.add(heartGlowMesh);
            heartGroup.add(heartMesh);
            scene.add(heartGroup);

            gsap.to(heartGroup.position, {
                y: "+=0.3",
                duration: 3.6,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut"
            });

            // ==========================================
            // 4. ✈️ 3D MODERN AIRBUS A350 IN CRUISING FLIGHT
            // ==========================================
            const airplaneGroup = new THREE.Group();
            const airplaneBankGroup = new THREE.Group();
            airplaneGroup.add(airplaneBankGroup);
            scene.add(airplaneGroup);

            // Aspect ratio: 1400 / 557 = 2.513
            const planeWidth = 5.2;
            const planeHeight = planeWidth / 2.513;
            const planeGeometry = new THREE.PlaneGeometry(planeWidth, planeHeight, 1, 1);

            const planeMaterial = new THREE.MeshStandardMaterial({
                transparent: true,
                alphaTest: 0.02,
                roughness: 0.42,
                metalness: 0.15,
                depthWrite: false,
                side: THREE.DoubleSide
            });

            const planeMesh = new THREE.Mesh(planeGeometry, planeMaterial);
            airplaneBankGroup.add(planeMesh);

            const shadowMaterial = new THREE.MeshBasicMaterial({
                transparent: true,
                color: 0x0284c7,
                opacity: 0.16,
                depthWrite: false
            });
            const shadowMesh = new THREE.Mesh(planeGeometry, shadowMaterial);
            shadowMesh.position.set(-0.18, -0.25, -0.15);
            shadowMesh.scale.set(1.01, 1.01, 1);
            airplaneBankGroup.add(shadowMesh);

            // 🚀 PARALLEL ASSET PRELOAD: Start loading texture asynchronously
            const textureLoader = new THREE.TextureLoader();
            const texturePromise = loadTextureAsync(textureLoader, '/images/plane_modern_a350.webp');

            // Apply texture as soon as its Promise resolves without halting scene initialization
            texturePromise.then((planeTexture) => {
                planeTexture.generateMipmaps = true;
                planeTexture.minFilter = THREE.LinearMipmapLinearFilter;
                planeTexture.magFilter = THREE.LinearFilter;
                if (THREE.sRGBEncoding) planeTexture.encoding = THREE.sRGBEncoding;

                planeMaterial.map = planeTexture;
                planeMaterial.needsUpdate = true;
                shadowMaterial.map = planeTexture;
                shadowMaterial.needsUpdate = true;
            }).catch((err) => {
                console.warn('[Airplane3D] Texture load error:', err);
            });

            // ==========================================
            // 5. 💨 REALISTIC WISPY JET ENGINE CONTRAILS
            // ==========================================
            const puffCanvas = document.createElement('canvas');
            puffCanvas.width = 128;
            puffCanvas.height = 128;
            const pCtx = puffCanvas.getContext('2d');
            const pGrad = pCtx.createRadialGradient(64, 64, 0, 64, 64, 64);
            pGrad.addColorStop(0, 'rgba(255, 255, 255, 0.65)');
            pGrad.addColorStop(0.25, 'rgba(240, 249, 255, 0.40)');
            pGrad.addColorStop(0.55, 'rgba(224, 242, 254, 0.16)');
            pGrad.addColorStop(0.85, 'rgba(224, 242, 254, 0.04)');
            pGrad.addColorStop(1, 'rgba(255, 255, 255, 0)');
            pCtx.fillStyle = pGrad;
            pCtx.beginPath();
            pCtx.arc(64, 64, 64, 0, Math.PI * 2);
            pCtx.fill();

            const puffTexture = new THREE.CanvasTexture(puffCanvas);
            if (THREE.sRGBEncoding) puffTexture.encoding = THREE.sRGBEncoding;

            const MAX_PARTICLES = 160;
            const particles = [];
            const particleGroup = new THREE.Group();
            scene.add(particleGroup);

            const particleGeo = new THREE.PlaneGeometry(0.8, 0.8);
            const particleMat = new THREE.MeshBasicMaterial({
                map: puffTexture,
                transparent: true,
                opacity: 0,
                depthWrite: false,
                blending: THREE.NormalBlending
            });

            for (let i = 0; i < MAX_PARTICLES; i++) {
                const pMesh = new THREE.Mesh(particleGeo, particleMat.clone());
                pMesh.visible = false;
                particleGroup.add(pMesh);
                particles.push({
                    mesh: pMesh,
                    active: false,
                    age: 0,
                    maxAge: 75 + Math.random() * 35,
                    scale: 0.15,
                    growRate: 1.026 + Math.random() * 0.010,
                    vx: -0.008 + (Math.random() - 0.5) * 0.015,
                    vy: (Math.random() - 0.5) * 0.012,
                    vz: (Math.random() - 0.5) * 0.015
                });
            }

            let particleCursor = 0;
            function spawnContrail(localX, localY, localZ) {
                const p = particles[particleCursor];
                particleCursor = (particleCursor + 1) % MAX_PARTICLES;

                const worldPos = new THREE.Vector3(localX, localY, localZ);
                airplaneBankGroup.localToWorld(worldPos);

                worldPos.x += (Math.random() - 0.5) * 0.04;
                worldPos.y += (Math.random() - 0.5) * 0.04;
                worldPos.z += (Math.random() - 0.5) * 0.04;

                p.mesh.position.copy(worldPos);
                p.mesh.rotation.z = Math.random() * Math.PI * 2;
                p.scale = 0.15;
                p.mesh.scale.setScalar(p.scale);
                p.mesh.material.opacity = 0.48;
                p.mesh.visible = true;
                p.age = 0;
                p.active = true;
            }

            // ==========================================
            // 6. 🎬 GSAP MASTER FLIGHT TIMELINE
            // ==========================================
            let isFlightVisible = false;

            gsap.to(airplaneBankGroup.rotation, {
                z: "+=0.032",
                x: "+=0.024",
                duration: 2.2,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut"
            });

            const engineL = { x: -0.22, y: -0.82, z: -0.05 };
            const engineR = { x: 1.67, y: -0.53, z: -0.05 };

            const flightProxy = {
                x: -24,
                y: -3.5,
                z: -1.5,
                rotZ: 0.22,
                rotY: -0.10,
                rotX: 0.12
            };

            const flightTimeline = gsap.timeline({
                repeat: -1,
                repeatDelay: 1.2,
                onUpdate: function () {
                    airplaneGroup.position.set(flightProxy.x, flightProxy.y, flightProxy.z);
                    airplaneBankGroup.rotation.z = flightProxy.rotZ;
                    airplaneBankGroup.rotation.y = flightProxy.rotY;
                    airplaneBankGroup.rotation.x = flightProxy.rotX;

                    if (isFlightVisible && Math.random() < 0.85) {
                        spawnContrail(engineL.x, engineL.y, engineL.z);
                        spawnContrail(engineR.x, engineR.y, engineR.z);
                    }
                }
            });

            flightTimeline.set(flightProxy, {
                x: -24,
                y: -3.4,
                z: -1.8,
                rotZ: 0.22,
                rotY: -0.12,
                rotX: 0.14
            }, 0);

            flightTimeline.call(() => { isFlightVisible = true; }, null, 0.05);

            flightTimeline.to(flightProxy, {
                x: 0,
                y: 0.8,
                z: 0.6,
                rotZ: 0.05,
                rotY: 0.02,
                rotX: 0.06,
                duration: 4.8,
                ease: "power1.inOut"
            }, 0);

            flightTimeline.to(heartMaterial, {
                opacity: 0.92,
                duration: 1.6,
                yoyo: true,
                repeat: 1,
                ease: "sine.inOut"
            }, 3.5);

            flightTimeline.to(flightProxy, {
                x: 24,
                y: 4.5,
                z: -4.0,
                rotZ: -0.10,
                rotY: 0.12,
                rotX: -0.03,
                duration: 5.4,
                ease: "power1.inOut"
            }, 4.8);

            flightTimeline.call(() => { isFlightVisible = false; }, null, 10.2);

            // ==========================================
            // 7. 🖱️ INTERACTIVE MOUSE 3D PARALLAX
            // ==========================================
            let targetCamX = 0;
            let targetCamY = 0;

            heroBanner.addEventListener('mousemove', function (e) {
                const rect = heroBanner.getBoundingClientRect();
                const normX = (e.clientX - rect.left) / rect.width - 0.5;
                const normY = (e.clientY - rect.top) / rect.height - 0.5;
                targetCamX = normX * 1.8;
                targetCamY = -normY * 1.2;
            }, { passive: true });

            heroBanner.addEventListener('mouseleave', function () {
                targetCamX = 0;
                targetCamY = 0;
            });

            // ==========================================
            // 8. 🔄 RENDER LOOP & PERFORMANCE OPTIMIZATION
            // ==========================================
            let isPaused = false;
            let animationFrameId = null;

            function animate() {
                if (!isPaused) {
                    camera.position.x += (targetCamX - camera.position.x) * 0.06;
                    camera.position.y += (targetCamY - camera.position.y) * 0.06;
                    camera.lookAt(0, 0.4, 0);

                    for (let i = 0; i < MAX_PARTICLES; i++) {
                        const p = particles[i];
                        if (!p.active) continue;

                        p.age++;
                        if (p.age >= p.maxAge) {
                            p.active = false;
                            p.mesh.visible = false;
                            continue;
                        }

                        p.scale *= p.growRate;
                        p.mesh.scale.setScalar(p.scale);
                        p.mesh.position.x += p.vx;
                        p.mesh.position.y += p.vy;
                        p.mesh.position.z += p.vz;

                        const progress = p.age / p.maxAge;
                        p.mesh.material.opacity = Math.max(0, (1 - progress) * 0.48);
                    }

                    renderer.render(scene, camera);
                }
                animationFrameId = requestAnimationFrame(animate);
            }

            animate();

            function onResize() {
                width = stage.clientWidth || heroBanner.clientWidth || window.innerWidth;
                height = stage.clientHeight || heroBanner.clientHeight || 560;
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
                renderer.setSize(width, height);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            }

            window.addEventListener('resize', onResize, { passive: true });

            // IntersectionObserver: Pause 100% when scrolled out of view
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            isPaused = false;
                            flightTimeline.resume();
                        } else {
                            isPaused = true;
                            flightTimeline.pause();
                        }
                    });
                }, { threshold: 0.02 });

                observer.observe(heroBanner);
            }
        } catch (error) {
            console.warn('[HeroAirplane3D] Service initialization skipped:', error);
        }
    }

    // Launch immediately via Promise without blocking DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initAirplaneEngineAsync();
        });
    } else {
        initAirplaneEngineAsync();
    }
})();

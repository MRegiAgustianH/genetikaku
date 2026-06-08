import { motion, useReducedMotion } from 'motion/react';

/**
 * Konfigurasi sel darah yang mengapung. Disusun ZIGZAG secara vertikal
 * (offset kiri berselang-seling) dan masing-masing mengambang naik-turun
 * dengan durasi/jeda berbeda agar terlihat alami.
 */
const CELLS = [
    { src: '/images/blood1.png', top: '8%', left: '10%', size: 96, range: 22, duration: 6, delay: 0 },
    { src: '/images/blood2.png', top: '34%', left: '34%', size: 72, range: 18, duration: 5, delay: 0.6 },
    { src: '/images/blood3.png', top: '58%', left: '8%', size: 84, range: 26, duration: 7, delay: 1.1 },
    { src: '/images/blood1.png', top: '78%', left: '38%', size: 60, range: 16, duration: 5.5, delay: 0.3 },
];

/**
 * Dekorasi sel darah mengapung di sisi kiri hero beranda.
 *
 * Murni dekoratif (aria-hidden, pointer-events-none) dan disembunyikan pada
 * layar kecil agar tidak mengganggu konten. Menghormati preferensi
 * reduced-motion: bila aktif, sel tampil statis tanpa animasi.
 */
export default function FloatingBlood() {
    const reduce = useReducedMotion();

    return (
        <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-y-0 left-0 z-[1] hidden w-1/3 max-w-sm md:block"
        >
            {CELLS.map((cell, index) => (
                <motion.img
                    key={index}
                    src={cell.src}
                    alt=""
                    width={cell.size}
                    height={cell.size}
                    style={{ top: cell.top, left: cell.left, width: cell.size, height: 'auto' }}
                    className="absolute drop-shadow-[0_8px_20px_rgba(190,80,90,0.25)]"
                    initial={{ opacity: 0, scale: 0.8 }}
                    animate={
                        reduce
                            ? { opacity: 0.85, scale: 1 }
                            : {
                                  opacity: 0.9,
                                  scale: 1,
                                  y: [0, -cell.range, 0, cell.range, 0],
                                  rotate: [0, 6, 0, -6, 0],
                              }
                    }
                    transition={
                        reduce
                            ? { duration: 0.4 }
                            : {
                                  y: { duration: cell.duration, repeat: Infinity, ease: 'easeInOut', delay: cell.delay },
                                  rotate: { duration: cell.duration * 1.4, repeat: Infinity, ease: 'easeInOut', delay: cell.delay },
                                  opacity: { duration: 0.6, delay: cell.delay },
                                  scale: { duration: 0.6, delay: cell.delay },
                              }
                    }
                />
            ))}
        </div>
    );
}

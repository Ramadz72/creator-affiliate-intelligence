// ─────────────────────────────────────────────────────────────────────────────
// landing/data.ts — SEMUA konten halaman welcome ada di sini.
// Ganti teks dan gambar di file ini tanpa menyentuh komponen.
//
// Catatan porting ke Vue: file ini murni data + tipe — bisa langsung dipakai
// ulang sebagai modul konstanta di Vue (mis. src/landing/data.ts). Tiap
// komponen di src/components/landing/ dipetakan 1:1 menjadi SFC Vue:
//   Navbar.tsx → Navbar.vue, Hero.tsx → Hero.vue, dst.
// ─────────────────────────────────────────────────────────────────────────────

export interface ReviewItem {
  id: string;
  name: string;
  handle: string;
  category: string;
  quote: string;
  metric: string;
  image: string;
}

export interface FeatureItem {
  icon: string;
  title: string;
  desc: string;
}

export interface FaqEntry {
  question: string;
  answer: string;
}

export interface PlatformItem {
  id: string;
  name: string;
  badge: string;
}

export const HERO = {
  eyebrow: "Platform Analitik untuk Pemilik Toko",
  titleLine1: "Kolaborasi creator itu mahal.",
  titleLine2: "Pastikan sebanding dengan hasilnya.",
  body: "Creator Affiliate Intelligence menjawab dua pertanyaan bisnis terpenting toko Anda: apakah biaya kolaborasi dengan seorang creator sebanding dengan value yang diberikan, dan affiliate mana yang layak menjadi prioritas untuk dikejar dan diberi support.",
  actionCard: {
    title: "Dua Analisis Utama",
    lines: ["Creator Collaboration Analysis", "Affiliate Opportunity Analysis"],
    detail: "Skor berbobot transparan — setiap keputusan bisa Anda jelaskan.",
    cta: "Buka Dashboard",
    href: "/dashboard",
  },
  dashboard: {
    title: "Creator & Affiliate Intelligence",
    badge: "Live",
    metricLabel: "Est. ROI Kolaborasi Terbaik",
    metricValue: "3,4x",
    metricTrend: "+12 creator dianalisis",
    winnerLabel: "Creator Terbaik",
    winnerValue: "Anisa Beauty — Highly Recommended",
    winnerCvr: "Skor 87/100",
    aiLabel: "Affiliate Prioritas",
    aiValue: "5 affiliate masuk kategori CHASE minggu ini",
  },
};

// Sparkline estimasi value kolaborasi per pekan (juta Rupiah).
export const VALUE_SERIES: { time: string; value: number }[] = [
  { time: "P1", value: 1.8 },
  { time: "P2", value: 2.4 },
  { time: "P3", value: 2.2 },
  { time: "P4", value: 3.0 },
  { time: "P5", value: 2.8 },
  { time: "P6", value: 3.4 },
  { time: "P7", value: 3.9 },
  { time: "P8", value: 4.6 },
];

export const PLATFORMS_TITLE = "Menganalisis creator & affiliate di seluruh platform utama";

export const PLATFORMS: PlatformItem[] = [
  { id: "tiktok", name: "TikTok Affiliate & Shop", badge: "Analytics Sync" },
  { id: "shopee", name: "Shopee Affiliate Program", badge: "Commission Tracking" },
  { id: "tokopedia", name: "Tokopedia / TikTok Shop", badge: "Product Feed" },
  { id: "instagram", name: "Instagram Reels", badge: "Content Attribution" },
];

export const REVIEWS: ReviewItem[] = [
  {
    id: "rev-1",
    name: "Hendra Saputra",
    handle: "@hendra.fashion78",
    category: "Owner Butik Online, Bandung",
    quote:
      "Affiliate Opportunity Analysis menunjukkan siapa yang harus saya kejar, diberi support, atau dilepas. Tim saya berhenti membuang waktu ke affiliate yang tidak berkembang.",
    metric: "GMV dari affiliate naik 58%",
    image:
      "https://images.pexels.com/photos/6333689/pexels-photo-6333689.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
  },
  {
    id: "rev-2",
    name: "Rina Wijaya",
    handle: "@tokoskincareku",
    category: "Owner Toko Skincare, Jakarta",
    quote:
      "Dulu saya booking KOL lima juta berdasarkan feeling. Sekarang tiap creator dianalisis dulu: performa, rate card, sampai estimasi value-nya. Yang tidak masuk akal tinggal saya negosiasi atau lewat.",
    metric: "ROI kolaborasi naik 3,4x dalam 3 bulan",
    image:
      "https://images.pexels.com/photos/7676404/pexels-photo-7676404.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
  },
  {
    id: "rev-3",
    name: "Don Putra",
    handle: "@donputra.store",
    category: "Pemilik Brand Herbal & Health",
    quote:
      "Rate card creator skincare besar bisa 9 juta per konten. Aplikasi ini menghitung estimasi value-nya dan memberi verdict Negotiate — jadi saya tahu angka wajar untuk menawar.",
    metric: "Biaya KOL turun 30% dengan hasil setara",
    image:
      "https://images.unsplash.com/photo-1623658877772-69ae99a89304?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDk1ODF8MHwxfHNlYXJjaHw0fHxjcmVhdG9yJTIwd29ya2luZyUyMGxhcHRvcCUyMGluZG9uZXNpYXxlbnwwfHx8fDE3OTAwODY2NDJ8MA&ixlib=rb-4.1.0&q=85",
  },
  {
    id: "rev-4",
    name: "Bagus & Lia",
    handle: "@dapurprodukmama",
    category: "Owner Brand Rumahan",
    quote:
      "Kami mengelola 40+ affiliate. Yang dulu manual 8 jam di spreadsheet, sekarang 10 menit: daftar CHASE, SUPPORT, MONITOR, dan DEPRIORITIZE sudah jadi.",
    metric: "Waktu analisis turun dari 8 jam ke 10 menit",
    image:
      "https://images.unsplash.com/photo-1707991396652-cf7827abe722?crop=entropy&cs=srgb&fm=jpg&ixid=M3w4NTYxOTF8MHwxfHNlYXJjaHwxfHxjb250ZW50JTIwY3JlYXRvciUyMHN0dWRpbyUyMGFuYWx5dGljc3xlbnwwfHx8fDE3OTAwODY2NDJ8MA&ixlib=rb-4.1.0&q=85",
  },
];

export const FEATURES_HEADING = "Dua Analisis untuk Dua Pertanyaan Bisnis Anda";
export const FEATURES_SUB =
  "Dirancang untuk pemilik toko yang ingin memutuskan kolaborasi creator dan memprioritaskan affiliate dengan data — bukan feeling.";

export const FEATURES: FeatureItem[] = [
  {
    icon: "handshake",
    title: "Creator Collaboration Analysis",
    desc: "Creator performance, rate card, relevansi brand, dan estimated value digabung menjadi satu verdict: Highly Recommended, Recommended, Negotiate, atau Not Recommended.",
  },
  {
    icon: "target",
    title: "Affiliate Opportunity Analysis",
    desc: "Performance, growth, consistency, dan potential dipadukan menjadi satu aksi yang jelas: CHASE, SUPPORT, MONITOR, atau DEPRIORITIZE.",
  },
  {
    icon: "calculator",
    title: "Rate Card vs Estimated Value",
    desc: "Estimasi value tiap konten dihitung dari reach, engagement, asumsi CVR, dan AOV — lalu dibandingkan langsung dengan rate card sebagai ROI.",
  },
  {
    icon: "sliders",
    title: "Skor Berbobot Transparan",
    desc: "Setiap bobot faktor, asumsi, dan ambang batas bisa Anda atur sendiri. Tidak ada kotak hitam — semua keputusan bisa dijelaskan ke tim.",
  },
  {
    icon: "database",
    title: "Data Tersimpan Rapi",
    desc: "Semua creator dan affiliate tersimpan di dashboard Anda — tambah, ubah, hapus, dan bandingkan kapan saja dalam satu tabel terurut skor.",
  },
  {
    icon: "lightbulb",
    title: "Keputusan Siap Aksi",
    desc: "Berhenti menebak: siapa creator yang layak di-booking, siapa affiliate yang harus dikejar minggu ini, dan mana yang cukup dipantau saja.",
  },
];

export const FAQ_HEADING = "Pertanyaan yang Sering Diajukan";
export const FAQ_SUB =
  "Punya keraguan? Temukan jawaban seputar cara kerja analisis, sumber perhitungan skor, dan keamanan data toko Anda.";

export const FAQS: FaqEntry[] = [
  {
    question: "Apa bedanya Creator Collaboration Analysis dan Affiliate Opportunity Analysis?",
    answer:
      "Creator Collaboration Analysis menilai apakah biaya kolaborasi dengan seorang creator sebanding dengan performa dan estimated value-nya — hasilnya berupa verdict mulai dari Highly Recommended sampai Not Recommended. Affiliate Opportunity Analysis meringkatkan affiliate yang sudah menjual produk Anda menjadi aksi: CHASE, SUPPORT, MONITOR, atau DEPRIORITIZE.",
  },
  {
    question: "Bagaimana skor dan verdict dihitung?",
    answer:
      "Semua skor dihitung dengan rumus berbobot yang transparan: setiap faktor (performa, rate card, relevansi brand, growth, konsistensi, potential) dinormalisasi ke skala 0-100 lalu digabung sesuai bobotnya. Bobot, asumsi, dan ambang batas bisa Anda lihat dan ubah sendiri di tab Pengaturan Skor.",
  },
  {
    question: "Dari mana Estimated Value seorang creator dihitung?",
    answer:
      "Estimated value = rata-rata views × engagement rate × asumsi CVR × asumsi nilai transaksi (AOV). Nilai inilah yang dibandingkan dengan rate card creator untuk menghitung ROI kolaborasi. Asumsi CVR dan AOV bisa disesuaikan dengan kategori produk toko Anda.",
  },
  {
    question: "Apakah data toko saya aman?",
    answer:
      "Aman. Data creator dan affiliate Anda tersimpan di database aplikasi Anda sendiri, hanya diakses oleh dashboard Anda, dan tidak dibagikan ke pihak ketiga.",
  },
  {
    question: "Apakah aplikasi ini berbayar?",
    answer:
      "Tidak. Seluruh fitur analisis tersedia tanpa biaya dan tanpa pembatasan paket — langsung buka dashboard dan mulai menganalisis.",
  },
];

export const FINAL_CTA = {
  heading: "Berhenti Menebak, Mulai Putuskan dengan Data",
  sub: "Buka dashboard, masukkan data creator dan affiliate Anda, dan lihat verdict serta prioritas tindakannya dalam hitungan menit.",
  primaryLabel: "Mulai Analisis Sekarang",
  primaryHref: "/dashboard",
  secondaryLabel: "Sudah punya akun? Masuk di sini →",
  secondaryHref: "/login",
  footnote: "Tanpa biaya. Skor berbobot transparan — atur sesuai karakter toko Anda.",
};

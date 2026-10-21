<?php

namespace App\Support;

/**
 * Sumber tunggal data FAQ.
 *
 * Dipakai oleh halaman FAQ (resources/js/pages/FAQ/Index.vue) sekaligus
 * untuk membangun structured data FAQPage di dalam <head> halaman.
 */
class Faq
{
    /**
     * @return list<array{id: int, category: string, question: string, answer: string}>
     */
    public static function items(): array
    {
        return [
        [
            'id' => 1,
            'category' => 'General',
            'question' => 'Apa itu Creator Affiliate Intelligence?',
            'answer' => 'Creator Affiliate Intelligence adalah aplikasi untuk membantu menganalisis performa creator atau KOL serta performa affiliate berdasarkan data yang tersedia. Aplikasi membantu mengubah data performa menjadi informasi, scoring, insight, dan rekomendasi tindakan yang lebih mudah dipahami.',
        ],
        [
            'id' => 2,
            'category' => 'General',
            'question' => 'Apa saja fitur utama aplikasi?',
            'answer' => 'Fitur utama aplikasi meliputi Dashboard, Creator / KOL Analysis, Affiliate Performance Analysis, Affiliate Index, Insights, Performance Movement, scoring, rekomendasi action, import data Seller Center, serta histori performa.',
        ],
        [
            'id' => 3,
            'category' => 'General',
            'question' => 'Siapa yang dapat menggunakan aplikasi ini?',
            'answer' => 'Aplikasi ditujukan untuk membantu tim yang menangani creator, KOL, affiliate, campaign, dan evaluasi performa agar dapat mengambil keputusan berdasarkan data.',
        ],
        [
            'id' => 4,
            'category' => 'Creator / KOL',
            'question' => 'Apa fungsi Creator / KOL Analysis?',
            'answer' => 'Creator / KOL Analysis digunakan untuk membantu mengevaluasi kelayakan seorang creator atau KOL sebelum digunakan untuk kebutuhan campaign atau kolaborasi.',
        ],
        [
            'id' => 5,
            'category' => 'Creator / KOL',
            'question' => 'Data apa saja yang dapat dianalisis dari creator?',
            'answer' => 'Data creator dapat mencakup informasi profil, performa, rate card, serta metrik lain yang tersedia pada form analisis creator. Data tersebut digunakan sebagai dasar evaluasi dan scoring.',
        ],
        [
            'id' => 6,
            'category' => 'Creator / KOL',
            'question' => 'Apa fungsi Rate Card pada Creator Analysis?',
            'answer' => 'Rate Card digunakan untuk mencatat biaya atau harga kerja sama creator berdasarkan jenis konten atau layanan yang ditawarkan. Informasi tersebut dapat digunakan sebagai pertimbangan bersama dengan performa creator.',
        ],
        [
            'id' => 7,
            'category' => 'Creator / KOL',
            'question' => 'Apakah Creator Analysis sama dengan Affiliate Analysis?',
            'answer' => 'Tidak. Creator / KOL Analysis berfokus pada evaluasi creator untuk kebutuhan kolaborasi atau campaign, sedangkan Affiliate Analysis berfokus pada performa affiliate berdasarkan data performa yang diimpor.',
        ],
        [
            'id' => 8,
            'category' => 'Affiliate',
            'question' => 'Apa itu Affiliate Performance Analysis?',
            'answer' => 'Affiliate Performance Analysis digunakan untuk mengevaluasi performa affiliate berdasarkan data performa seperti GMV, orders, buyers, content activity, CTR, CTOR, dan metrik lainnya.',
        ],
        [
            'id' => 9,
            'category' => 'Affiliate',
            'question' => 'Apa fungsi Affiliate Index?',
            'answer' => 'Affiliate Index merupakan halaman utama untuk melihat daftar affiliate beserta performa, movement, scoring, opportunity, dan recommended action. Data dapat difilter berdasarkan periode, pencarian, action, dan sorting.',
        ],
        [
            'id' => 10,
            'category' => 'Affiliate',
            'question' => 'Apa yang dimaksud Performance Movement?',
            'answer' => 'Performance Movement menunjukkan perubahan performa affiliate dibandingkan periode pembanding. Informasi ini membantu melihat affiliate yang performanya meningkat atau menurun.',
        ],
        [
            'id' => 11,
            'category' => 'Affiliate',
            'question' => 'Apa yang dimaksud Top Performer?',
            'answer' => 'Top Performer menunjukkan affiliate dengan performa terbaik berdasarkan hasil analisis pada periode yang sedang dipilih.',
        ],
        [
            'id' => 12,
            'category' => 'Affiliate',
            'question' => 'Apa yang dimaksud Low Consistency?',
            'answer' => 'Low Consistency menunjukkan affiliate yang performanya mengalami tingkat variasi yang relatif tinggi dari hari ke hari sehingga performanya belum stabil.',
        ],
        [
            'id' => 13,
            'category' => 'Affiliate',
            'question' => 'Apa yang dimaksud Potential Opportunity?',
            'answer' => 'Potential Opportunity menunjukkan affiliate yang memiliki peluang untuk dikembangkan berdasarkan kombinasi performa, pertumbuhan, dan faktor konsistensi yang tersedia.',
        ],
        [
            'id' => 14,
            'category' => 'Import Data',
            'question' => 'Dari mana data affiliate berasal?',
            'answer' => 'Data affiliate berasal dari data yang diekspor dari TikTok Seller Center dan kemudian diimpor ke dalam aplikasi.',
        ],
        [
            'id' => 15,
            'category' => 'Import Data',
            'question' => 'Format file apa yang digunakan untuk import?',
            'answer' => 'Import menggunakan file hasil export Seller Center dalam format spreadsheet yang didukung oleh sistem import aplikasi.',
        ],
        [
            'id' => 16,
            'category' => 'Import Data',
            'question' => 'Apa yang dimaksud dengan Tanggal Data?',
            'answer' => 'Tanggal Data merupakan tanggal performa yang terdapat pada file yang sedang diimpor. Untuk sistem daily snapshot, tanggal tersebut merepresentasikan data performa pada hari tertentu.',
        ],
        [
            'id' => 17,
            'category' => 'Import Data',
            'question' => 'Apakah data hari yang terlewat bisa diimport belakangan?',
            'answer' => 'Bisa. Tanggal performa dan tanggal upload merupakan dua hal yang berbeda. Data untuk tanggal sebelumnya tetap dapat diimpor kemudian selama file tersebut berisi data performa untuk tanggal yang sesuai.',
        ],
        [
            'id' => 18,
            'category' => 'Import Data',
            'question' => 'Bagaimana mengetahui proses import sudah selesai?',
            'answer' => 'Status import dapat dilihat melalui informasi batch. Sistem memproses data dan memperbarui progress hingga proses selesai atau gagal.',
        ],
        [
            'id' => 19,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa itu Performance Score?',
            'answer' => 'Performance Score merupakan skor yang menggambarkan kualitas performa affiliate berdasarkan beberapa komponen performa. Perhitungannya mempertimbangkan GMV, Orders, Buyers, Content Activity, dan Engagement.',
        ],
        [
            'id' => 20,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa itu Growth Score?',
            'answer' => 'Growth Score menggambarkan pertumbuhan GMV affiliate dibandingkan periode pembanding. Pertumbuhan positif akan meningkatkan score, sedangkan penurunan akan menurunkan score.',
        ],
        [
            'id' => 21,
            'category' => 'Scoring & Intelligence',
            'question' => 'Bagaimana Growth dihitung?',
            'answer' => 'Growth dihitung berdasarkan perubahan GMV antara periode saat ini dan periode sebelumnya dengan rumus: (GMV saat ini - GMV sebelumnya) / GMV sebelumnya × 100%.',
        ],
        [
            'id' => 22,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa itu Consistency Score?',
            'answer' => 'Consistency Score menunjukkan seberapa stabil performa GMV affiliate dari hari ke hari. Semakin kecil variasi performanya, semakin tinggi nilai konsistensinya.',
        ],
        [
            'id' => 23,
            'category' => 'Scoring & Intelligence',
            'question' => 'Kenapa Consistency Score bisa tampil —?',
            'answer' => 'Consistency membutuhkan minimal dua snapshot harian yang valid dalam periode analisis. Jika hanya tersedia satu hari data, sistem tidak memiliki cukup data untuk menghitung konsistensi.',
        ],
        [
            'id' => 24,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa itu Opportunity Score?',
            'answer' => 'Opportunity Score digunakan untuk membantu menentukan prioritas tindakan terhadap affiliate. Score mempertimbangkan Performance, Growth, dan Consistency ketika data yang diperlukan tersedia.',
        ],
        [
            'id' => 25,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa arti action CHASE?',
            'answer' => 'CHASE menunjukkan affiliate dengan opportunity yang sangat baik dan dapat menjadi prioritas untuk didorong atau dikembangkan lebih lanjut.',
        ],
        [
            'id' => 26,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa arti action SUPPORT?',
            'answer' => 'SUPPORT menunjukkan affiliate yang memiliki performa atau peluang yang cukup baik sehingga layak mendapatkan dukungan untuk meningkatkan hasilnya.',
        ],
        [
            'id' => 27,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa arti action MONITOR?',
            'answer' => 'MONITOR menunjukkan affiliate yang perlu dipantau perkembangannya sebelum menentukan tindakan lanjutan.',
        ],
        [
            'id' => 28,
            'category' => 'Scoring & Intelligence',
            'question' => 'Apa arti action DEPRIORITIZE?',
            'answer' => 'DEPRIORITIZE menunjukkan affiliate yang memiliki prioritas lebih rendah berdasarkan hasil scoring pada periode yang dipilih.',
        ],
        [
            'id' => 29,
            'category' => 'Dashboard & Insights',
            'question' => 'Apa fungsi Dashboard?',
            'answer' => 'Dashboard memberikan ringkasan kondisi performa secara keseluruhan, termasuk action center, performa affiliate, creator overview, grafik performa, serta informasi penting lainnya.',
        ],
        [
            'id' => 30,
            'category' => 'Dashboard & Insights',
            'question' => 'Apa fungsi Action Center?',
            'answer' => 'Action Center membantu menunjukkan prioritas yang membutuhkan perhatian. Setiap kategori dapat diarahkan langsung ke Affiliate Index dengan filter action dan periode yang sesuai.',
        ],
        [
            'id' => 31,
            'category' => 'Dashboard & Insights',
            'question' => 'Apa fungsi Insights?',
            'answer' => 'Insights memberikan ringkasan dan analisis yang lebih mendalam terhadap kondisi performa affiliate pada periode yang dipilih.',
        ],
        [
            'id' => 32,
            'category' => 'Dashboard & Insights',
            'question' => 'Apa yang dimaksud Comparison Period?',
            'answer' => 'Comparison Period adalah periode pembanding dengan durasi yang sama sebelum periode yang sedang dianalisis. Periode ini digunakan untuk melihat perubahan dan pertumbuhan performa.',
        ],
        [
            'id' => 33,
            'category' => 'Dashboard & Insights',
            'question' => 'Apakah periode analisis bisa dipilih sendiri?',
            'answer' => 'Bisa. Periode dapat disesuaikan berdasarkan tanggal yang ingin dianalisis. Sistem kemudian menentukan periode pembanding berdasarkan durasi periode tersebut.',
        ],
        ];
    }
}

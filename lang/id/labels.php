<?php

declare(strict_types=1);

return [
    // Common
    'actions' => 'Aksi',
    'search' => 'Cari',
    'filter' => 'Filter',
    'no' => 'No.',
    'items_selected' => ':count dipilih',
    'clear_selection' => 'Bersihkan',
    'no_failed_jobs' => 'Tidak ada job gagal.',
    'no_pending_jobs' => 'Tidak ada job tertunda.',

    // Page Titles
    'failed_jobs_list' => 'Job Gagal',
    'pending_jobs_list' => 'Job Tertunda',
    'worker_control' => 'Kontrol Worker',

    // Column Headers
    'job_class' => 'Job',
    'queue' => 'Antrean',
    'failed_at' => 'Gagal Pada',
    'attempts' => 'Percobaan',
    'available_at' => 'Tersedia Pada',
    'created_at' => 'Dibuat Pada',
    'exception' => 'Exception',

    // Buttons
    'retry' => 'Coba Lagi',
    'retry_selected' => 'Coba Lagi yang Dipilih',
    'delete' => 'Hapus',
    'delete_selected' => 'Hapus yang Dipilih',
    'flush_all' => 'Hapus Semua',
    'clear_queue' => 'Kosongkan Antrean',
    'restart_workers' => 'Restart Worker',

    // Confirm Dialog: Retry
    'confirm_retry_title' => 'Coba Lagi Job',
    'confirm_retry_message' => 'Apakah Anda yakin ingin mencoba lagi job ":name"?',
    'confirm_retry_selected_title' => 'Coba Lagi Job yang Dipilih',
    'confirm_retry_selected_message' => 'Apakah Anda yakin ingin mencoba lagi :count job yang dipilih?',
    'confirm_retry_confirm' => 'Ya, Coba Lagi',
    'confirm_retry_cancel' => 'Batal',

    // Confirm Dialog: Delete (failed job)
    'confirm_delete_title' => 'Hapus Job Gagal',
    'confirm_delete_message' => 'Apakah Anda yakin ingin menghapus job ":name"? Tindakan ini tidak dapat dibatalkan.',
    'confirm_delete_selected_title' => 'Hapus Job yang Dipilih',
    'confirm_delete_selected_message' => 'Apakah Anda yakin ingin menghapus :count job yang dipilih? Tindakan ini tidak dapat dibatalkan.',
    'confirm_delete_confirm' => 'Ya, Hapus',
    'confirm_delete_cancel' => 'Batal',

    // Confirm Dialog: Flush All (failed jobs)
    'confirm_flush_all_title' => 'Hapus Semua Job Gagal',
    'confirm_flush_all_message' => 'Apakah Anda yakin ingin menghapus SEMUA job gagal? Tindakan ini tidak dapat dibatalkan.',
    'confirm_flush_all_confirm' => 'Ya, Hapus Semua',
    'confirm_flush_all_cancel' => 'Batal',

    // Confirm Dialog: Delete (pending job)
    'confirm_delete_pending_title' => 'Hapus Job Tertunda',
    'confirm_delete_pending_message' => 'Apakah Anda yakin ingin menghapus job ":name" dari antrean ":queue"? Tindakan ini tidak dapat dibatalkan.',
    'confirm_delete_pending_selected_title' => 'Hapus Job Tertunda yang Dipilih',
    'confirm_delete_pending_selected_message' => 'Apakah Anda yakin ingin menghapus :count job tertunda yang dipilih? Tindakan ini tidak dapat dibatalkan.',

    // Confirm Dialog: Clear Queue (pending jobs)
    'confirm_clear_queue_title' => 'Kosongkan Antrean',
    'confirm_clear_queue_message' => 'Apakah Anda yakin ingin menghapus SEMUA job tertunda dari antrean? Tindakan ini tidak dapat dibatalkan.',
    'confirm_clear_queue_confirm' => 'Ya, Kosongkan Antrean',
    'confirm_clear_queue_cancel' => 'Batal',

    // Confirm Dialog: Restart Workers
    'confirm_restart_title' => 'Restart Worker',
    'confirm_restart_message' => 'Apakah Anda yakin ingin memberi sinyal restart ke semua worker antrean?',
    'confirm_restart_confirm' => 'Ya, Restart',
    'confirm_restart_cancel' => 'Batal',

    // Notifications: Failed Jobs
    'job_retried' => 'Job dimasukkan ke antrean untuk dicoba lagi.',
    'job_retry_failed' => 'Gagal mencoba lagi job.',
    'jobs_retried' => ':count job dimasukkan ke antrean untuk dicoba lagi.',
    'job_deleted' => 'Job gagal dihapus.',
    'job_delete_failed' => 'Gagal menghapus job.',
    'jobs_deleted' => ':count job dihapus.',
    'jobs_flushed' => 'Semua job gagal telah dihapus.',
    'jobs_flush_failed' => 'Gagal menghapus semua job.',

    // Notifications: Pending Jobs
    'pending_job_deleted' => 'Job tertunda dihapus.',
    'pending_job_delete_failed' => 'Gagal menghapus job tertunda.',
    'pending_jobs_deleted' => ':count job tertunda dihapus.',
    'queue_cleared' => 'Antrean berhasil dikosongkan.',
    'queue_clear_failed' => 'Gagal mengosongkan antrean.',

    // Notifications: Worker
    'workers_restarted' => 'Sinyal restart dikirim ke semua worker.',
    'workers_restart_failed' => 'Gagal mengirim sinyal restart worker.',
    'otp_invalid' => 'Kode verifikasi tidak valid atau kedaluwarsa. Silakan coba lagi.',

    // Worker Page Copy
    'current_driver' => 'Driver Antrean Saat Ini',
    'last_restarted_at' => 'Terakhir Di-restart Pada',
    'never_restarted' => 'Belum pernah di-restart',
    'supervisor_note' => 'Restart hanya memberi sinyal kepada worker yang berjalan agar berhenti dengan aman setelah job yang sedang dikerjakan selesai. Process supervisor (Supervisor, systemd, Horizon, dll.) harus dikonfigurasi untuk menjalankan ulang proses worker secara otomatis, atau antrean akan berhenti diproses sepenuhnya.',

    // Guards
    'pending_driver_warning' => 'Pengelolaan job tertunda hanya tersedia untuk driver antrean "database". Driver saat ini (:driver) tidak mendukung penampilan daftar job tertunda.',
];

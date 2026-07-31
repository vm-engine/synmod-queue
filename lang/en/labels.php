<?php

return [
    // Common
    'actions' => 'Actions',
    'search' => 'Search',
    'filter' => 'Filter',
    'no' => 'No.',
    'items_selected' => ':count selected',
    'clear_selection' => 'Clear',
    'no_failed_jobs' => 'No failed jobs found.',
    'no_pending_jobs' => 'No pending jobs found.',

    // Page Titles
    'failed_jobs_list' => 'Failed Jobs',
    'pending_jobs_list' => 'Pending Jobs',
    'worker_control' => 'Worker Control',

    // Column Headers
    'job_class' => 'Job',
    'queue' => 'Queue',
    'failed_at' => 'Failed At',
    'attempts' => 'Attempts',
    'available_at' => 'Available At',
    'created_at' => 'Created At',
    'exception' => 'Exception',

    // Buttons
    'retry' => 'Retry',
    'retry_selected' => 'Retry Selected',
    'delete' => 'Delete',
    'delete_selected' => 'Delete Selected',
    'flush_all' => 'Flush All',
    'clear_queue' => 'Clear Queue',
    'restart_workers' => 'Restart Workers',

    // Confirm Dialog: Retry
    'confirm_retry_title' => 'Retry Job',
    'confirm_retry_message' => 'Are you sure you want to retry job ":name"?',
    'confirm_retry_selected_title' => 'Retry Selected Jobs',
    'confirm_retry_selected_message' => 'Are you sure you want to retry :count selected job(s)?',
    'confirm_retry_confirm' => 'Yes, Retry',
    'confirm_retry_cancel' => 'Cancel',

    // Confirm Dialog: Delete (failed job)
    'confirm_delete_title' => 'Delete Failed Job',
    'confirm_delete_message' => 'Are you sure you want to delete job ":name"? This action cannot be undone.',
    'confirm_delete_selected_title' => 'Delete Selected Jobs',
    'confirm_delete_selected_message' => 'Are you sure you want to delete :count selected job(s)? This action cannot be undone.',
    'confirm_delete_confirm' => 'Yes, Delete',
    'confirm_delete_cancel' => 'Cancel',

    // Confirm Dialog: Flush All (failed jobs)
    'confirm_flush_all_title' => 'Flush All Failed Jobs',
    'confirm_flush_all_message' => 'Are you sure you want to delete ALL failed jobs? This action cannot be undone.',
    'confirm_flush_all_confirm' => 'Yes, Flush All',
    'confirm_flush_all_cancel' => 'Cancel',

    // Confirm Dialog: Delete (pending job)
    'confirm_delete_pending_title' => 'Delete Pending Job',
    'confirm_delete_pending_message' => 'Are you sure you want to delete job ":name" from queue ":queue"? This action cannot be undone.',
    'confirm_delete_pending_selected_title' => 'Delete Selected Pending Jobs',
    'confirm_delete_pending_selected_message' => 'Are you sure you want to delete :count selected pending job(s)? This action cannot be undone.',

    // Confirm Dialog: Clear Queue (pending jobs)
    'confirm_clear_queue_title' => 'Clear Queue',
    'confirm_clear_queue_message' => 'Are you sure you want to remove ALL pending jobs from the queue? This action cannot be undone.',
    'confirm_clear_queue_confirm' => 'Yes, Clear Queue',
    'confirm_clear_queue_cancel' => 'Cancel',

    // Confirm Dialog: Restart Workers
    'confirm_restart_title' => 'Restart Workers',
    'confirm_restart_message' => 'Are you sure you want to signal all queue workers to restart?',
    'confirm_restart_confirm' => 'Yes, Restart',
    'confirm_restart_cancel' => 'Cancel',

    // Notifications: Failed Jobs
    'job_retried' => 'Job queued for retry.',
    'job_retry_failed' => 'Failed to retry job.',
    'jobs_retried' => ':count job(s) queued for retry.',
    'job_deleted' => 'Failed job deleted.',
    'job_delete_failed' => 'Failed to delete job.',
    'jobs_deleted' => ':count job(s) deleted.',
    'jobs_flushed' => 'All failed jobs have been flushed.',
    'jobs_flush_failed' => 'Failed to flush jobs.',

    // Notifications: Pending Jobs
    'pending_job_deleted' => 'Pending job deleted.',
    'pending_job_delete_failed' => 'Failed to delete pending job.',
    'pending_jobs_deleted' => ':count pending job(s) deleted.',
    'queue_cleared' => 'Queue cleared successfully.',
    'queue_clear_failed' => 'Failed to clear queue.',

    // Notifications: Worker
    'workers_restarted' => 'Restart signal sent to all workers.',
    'workers_restart_failed' => 'Failed to signal worker restart.',
    'otp_invalid' => 'Invalid or expired verification code. Please try again.',

    // Worker Page Copy
    'current_driver' => 'Current Queue Driver',
    'last_restarted_at' => 'Last Restarted At',
    'never_restarted' => 'Never restarted',
    'supervisor_note' => 'Restarting only signals running workers to exit gracefully after their current job. A process supervisor (Supervisor, systemd, Horizon, etc.) must be configured to automatically relaunch the worker process, or the queue will stop processing entirely.',

    // Guards
    'pending_driver_warning' => 'Pending job management is only available for the "database" queue driver. The current driver (:driver) does not support listing pending jobs.',
];

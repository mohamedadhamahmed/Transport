{{-- ستايل بسيط خاص بشاشات النقليات (مكتوب هنا عشان يشتغل من غير npm run build) --}}
<style>
    .tr-input { width: 100%; border: 1px solid #d1d5db; border-radius: .5rem; padding: .5rem .75rem; font-size: .875rem; background: #fff; }
    .tr-input:focus { outline: none; border-color: #1456E8; box-shadow: 0 0 0 1px #1456E8; }
    .tr-label { display: block; font-size: .875rem; font-weight: 500; color: #374151; margin-bottom: .25rem; }
    .tr-badge { display: inline-block; padding: .15rem .55rem; border-radius: 9999px; font-size: .72rem; font-weight: 600; }
    .tr-badge-green { background: #ecfdf5; color: #047857; }
    .tr-badge-amber { background: #fffbeb; color: #b45309; }
    .tr-badge-gray  { background: #f3f4f6; color: #4b5563; }
    .tr-badge-red   { background: #fff1f2; color: #be123c; }
    .tr-btn { display: inline-flex; align-items: center; gap: .25rem; padding: .375rem .75rem; border-radius: .5rem; font-size: .75rem; font-weight: 500; transition: .15s; white-space: nowrap; }
    .tr-btn-blue { color: #1456E8; background: rgba(20,86,232,.1); } .tr-btn-blue:hover { background: rgba(20,86,232,.2); }
    .tr-btn-red { color: #e11d48; background: #fff1f2; } .tr-btn-red:hover { background: #ffe4e6; }
    .tr-btn-gray { color: #374151; background: #f3f4f6; } .tr-btn-gray:hover { background: #e5e7eb; }
    .tr-btn-green { color: #047857; background: #ecfdf5; } .tr-btn-green:hover { background: #d1fae5; }
    .tr-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    @media (min-width: 768px) { .tr-grid-2 { grid-template-columns: repeat(2, 1fr); } .tr-grid-3 { grid-template-columns: repeat(3, 1fr); } .tr-grid-4 { grid-template-columns: repeat(4, 1fr); } .tr-span-2 { grid-column: span 2; } .tr-span-3 { grid-column: span 3; } .tr-span-full { grid-column: 1 / -1; } }
    .tr-stat { background: #fff; border: 1px solid #f3f4f6; border-radius: .75rem; padding: 1rem; }
    .tr-stat .v { font-size: 1.25rem; font-weight: 700; color: #0F1B4C; }
    .tr-stat .l { font-size: .75rem; color: #6b7280; }
</style>

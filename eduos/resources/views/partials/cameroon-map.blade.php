<svg viewBox="0 0 320 360" width="100%" height="100%" role="img" aria-label="Cameroon map with active shipment route">
    {{-- Cameroon silhouette: Lake Chad tip, northern panhandle, broad centre, SW coast, straight east --}}
    <path d="M196 6
             L212 14 L208 30 L216 44 L206 60 L214 76 L204 92
             L212 104 L232 116 L246 136 L252 160 L248 190 L254 222 L250 258 L256 292 L248 318
             L216 322 L188 330 L156 326 L128 332
             L112 318 L96 306 L84 292 L76 276 L64 268 L52 272 L44 262
             L56 250 L70 252 L80 244 L74 232 L86 220 L96 206
             L92 188 L104 172 L112 152 L126 140 L138 120 L150 108 L162 96 L168 78 L176 58 L182 38 L188 20 Z"
          fill="#0D5C3B" opacity="0.94"/>
    {{-- Internal division hints --}}
    <g stroke="#063A2A" stroke-width="1" opacity="0.28" fill="none">
        <path d="M204 92 L168 120 L126 140"/>
        <path d="M212 104 L196 150 L176 196 L160 240"/>
        <path d="M96 206 L150 214 L200 208 L248 200"/>
        <path d="M84 292 L140 278 L196 276 L250 262"/>
        <path d="M176 58 L196 64 L206 60"/>
    </g>
    {{-- Route: Douala (gold) → central depot (white) → Maroua (red) --}}
    <path d="M86 268 Q118 240 150 216 T196 150 Q204 116 202 84"
          fill="none" stroke="#FCFBF7" stroke-width="2.4" stroke-dasharray="7 6" stroke-linecap="round"/>
    {{-- Gold pin: Douala --}}
    <g transform="translate(86 268)">
        <path d="M0 -20 a12 12 0 1 1 -0.01 0 M0 -8 L0 6" fill="#D4A017"/>
        <path d="M0 6 C-9 -4 -12 -10 -12 -16 A12 12 0 1 1 12 -16 C12 -10 9 -4 0 6 Z" fill="#D4A017" stroke="#FCFBF7" stroke-width="1.6"/>
        <circle cx="0" cy="-15" r="4.5" fill="#FCFBF7"/>
    </g>
    {{-- White waypoint with warehouse icon: centre --}}
    <g transform="translate(160 200)">
        <circle r="13" fill="#FCFBF7" stroke="#0D5C3B" stroke-width="1.6"/>
        <path d="M-6 4 V-2 L0 -6 L6 -2 V4 Z M-2.4 4 V0 H2.4 V4" fill="none" stroke="#0D5C3B" stroke-width="1.5"/>
    </g>
    {{-- Red pin: Maroua --}}
    <g transform="translate(202 82)">
        <path d="M0 6 C-9 -4 -12 -10 -12 -16 A12 12 0 1 1 12 -16 C12 -10 9 -4 0 6 Z" fill="#D32F2F" stroke="#FCFBF7" stroke-width="1.6"/>
        <path d="M-4.5 -18.5 h9 v7 h-9 z M0 -18.5 v7" stroke="#FCFBF7" stroke-width="1.2" fill="none"/>
    </g>
</svg>

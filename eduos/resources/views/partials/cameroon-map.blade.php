<svg viewBox="0 0 340 380" width="100%" height="100%" role="img" aria-label="Cameroon map with active shipment route">
    {{-- Cameroon silhouette: Lake Chad tip → NE panhandle → Adamaoua bulge → straight east → SE corner → south border → Douala estuary & Bakassi → NW highlands --}}
    <path id="cm" d="M190 6
        L201 12 L199 24 L206 34 L200 48 L207 60 L201 74 L208 86
        L220 98 L234 110 L245 126 L251 146 L253 168 L247 192
        L254 214 L250 240 L257 266 L252 292 L261 314 L254 332
        L228 336 L200 344 L170 340 L142 346 L118 340
        L104 326 L94 312 L84 298 L70 292 L52 296 L44 286
        L56 276 L70 278 L78 268 L72 256 L82 244 L90 230
        L86 212 L96 196 L102 176 L114 162 L124 144 L136 128
        L146 112 L154 94 L162 74 L170 52 L178 30 L184 16 Z"
        fill="#124A2C"/>
    {{-- textured overlay for depth --}}
    <use href="#cm" fill="none" stroke="#0B3A20" stroke-width="2"/>
    {{-- internal division boundaries (thin, lighter) --}}
    <g stroke="#2E6B47" stroke-width="1" fill="none" opacity="0.75">
        <path d="M200 88 L170 104 L142 124"/>
        <path d="M212 100 L200 132 L182 164 L168 196"/>
        <path d="M106 172 L150 178 L196 172 L250 194"/>
        <path d="M92 226 L140 224 L188 220 L254 244"/>
        <path d="M86 312 L136 296 L188 292 L256 300"/>
        <path d="M112 342 L146 316 L164 344"/>
        <path d="M168 196 L160 236 L150 272 L134 306"/>
        <path d="M172 52 L188 60 L198 58"/>
        <path d="M158 92 L178 96 L200 88"/>
        <path d="M196 172 L214 190 L226 214 L232 246"/>
        <path d="M188 292 L206 312 L226 340"/>
        <path d="M76 264 L104 258 L128 252 L150 246"/>
    </g>
    {{-- route: Douala (gold pin) → truck → central depot (dark circle) → Maroua (red pin) --}}
    <path d="M88 276 Q116 262 140 244 T176 196 Q192 164 196 132 T200 76"
          fill="none" stroke="#FCFBF7" stroke-width="2.6" stroke-dasharray="8 7" stroke-linecap="round"/>
    {{-- gold pin: Douala --}}
    <g transform="translate(88 276)">
        <path d="M0 8 C-10 -3 -13.5 -10 -13.5 -17 A13.5 13.5 0 1 1 13.5 -17 C13.5 -10 10 -3 0 8 Z" fill="#D4A017" stroke="#FCFBF7" stroke-width="2"/>
        <circle cx="0" cy="-16" r="5" fill="#FCFBF7"/>
    </g>
    {{-- truck waypoint: white rounded square, green truck --}}
    <g transform="translate(146 238)">
        <rect x="-17" y="-15" width="34" height="30" rx="8" fill="#FCFBF7" stroke="#124A2C" stroke-width="1.4"/>
        <g stroke="#124A2C" stroke-width="1.8" fill="none">
            <rect x="-10" y="-6" width="12" height="9" rx="1"/>
            <path d="M2 -3 h6 l4 4 v2 h-10 z"/>
            <circle cx="-5" cy="6" r="2.2"/><circle cx="7" cy="6" r="2.2"/>
        </g>
    </g>
    {{-- central depot: dark circle, white building --}}
    <g transform="translate(186 178)">
        <circle r="16" fill="#12201A" stroke="#FCFBF7" stroke-width="2"/>
        <g stroke="#FCFBF7" stroke-width="1.7" fill="none">
            <path d="M-8 6 V-3 L0 -8 L8 -3 V6 Z"/>
            <path d="M-3 6 V0 H3 V6"/>
        </g>
    </g>
    {{-- red pin with book: Maroua --}}
    <g transform="translate(200 72)">
        <path d="M0 9 C-11 -3 -15 -11 -15 -18 A15 15 0 1 1 15 -18 C15 -11 11 -3 0 9 Z" fill="#C62828" stroke="#FCFBF7" stroke-width="2"/>
        <g stroke="#FCFBF7" stroke-width="1.6" fill="none">
            <path d="M-6 -21 C-3 -23 -1 -23 0 -22 C1 -23 3 -23 6 -21 V-13 C3 -15 1 -15 0 -14 C-1 -15 -3 -15 -6 -13 Z"/>
            <path d="M0 -22 V-14"/>
        </g>
    </g>
</svg>

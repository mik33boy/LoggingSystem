<script lang="ts">
  export let active: string;
  export let role: string; // Add a prop for user role
</script>

<div class="h-screen w-16 bg-gray-900 flex flex-col items-center py-4 space-y-6">
  <!-- RSS -->
  <button class={`text-white ${active === 'rss' ? 'bg-gray-700 p-2 rounded-xl' : ''}`}>
    <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M5.88 41.979C9.12743 41.979 11.76 39.3465 11.76 36.099C11.76 32.8516 9.12743 30.219 5.88 30.219C2.63257 30.219 0 32.8516 0 36.099C0 39.3465 2.63257 41.979 5.88 41.979Z" fill="white"/>
      <path d="M0 15.267V22.911C10.542 22.911 19.0785 31.4685 19.0785 41.9895H26.7225C26.7225 27.237 14.7525 15.267 0 15.267Z" fill="white"/>
      <path d="M0 0V7.644C18.9735 7.644 34.356 23.0265 34.356 42H42C41.9895 18.795 23.184 0 0 0Z" fill="white"/>
    </svg>
  </button>

  <!-- Grid -->
  <button class={`text-white ${active === 'grid' ? 'bg-gray-700 p-2 rounded-xl' : ''}`}>
    <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
      <rect width="52" height="52" fill="url(#pattern0_73_127)"/>
      <defs>
        <pattern id="pattern0_73_127" patternContentUnits="objectBoundingBox" width="1" height="1">
          <use xlink:href="#image0_73_127" transform="scale(0.0111111)"/>
        </pattern>
        <image id="image0_73_127" width="90" height="90" preserveAspectRatio="none" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFoAAABaCAYAAAA4qEECAAAACXBIWXMAAAsTAAALEwEAmpwYAAACH0lEQVR4nO3cS07DQBCE4dkkZwgn5HUQzMFIeB2DhDUk+0JWBgkhR3Lb5R63p74DjKkfK4jIckoiIiIiIiIiIpIArAHcA9gBOGG5TgC2AO4ArFx/9QCuALyjPm8ANp53co2Rf7263Nn546J2tx6hn0uvnIGtR+hj6ZUz8O0RulNaKJTaq9BnCk2GC5Z74UIU2olCOwkTOv8n+QjgALs9gKY9o+D5pr001gvnIWM1Bc/vlKZmvfDAO+2/z4Lnd0pTs16YEGGW56epRQlhxdpLEyWEFWsvTZQQVqy9NFFCWLH20kQJYcXaSxMlhBVrL02UEFasvTRRQlix9tJECWHF2ksTJYQVay9NlBBWrL00A37QPcb7KHh+pzQ164VJX2M+FDy/U5qa9cL5i/lm4J3X94v/Kc837aUpduFCFNqJQjtRaCcK7UShnYQJrec6/EI3GE/PdfQIfSCE1nMdPUJTzO38NLUoIaxYe2mihLBi7aWJEsKKtZcmSggr1l6aKCGsWHtpooSwYu2liRLCirWXJkoIK9ZemighrFh7aaKEsGLtpYkSwoq1l2bAD7rHeHquo0fohhBaz3X0CL3Wcx0OHx3RodRehT5TaDJcsNwLF6LQThS6gtB6HRvw5RG6felr7Z48Qrdvna3djUfodX7rbK1e3F5v3L7aN791tsbIG5fIf2Kv2rfOtp9XC/8Decwbr91f1C0iIiIiIiIikubpB5FjMmJ2cBaOAAAAAElFTkSuQmCC"/>
      </defs>
    </svg>
  </button>

  <!-- Lock -->
  <button class={`text-white ${active === 'lock' ? 'bg-gray-700 p-2 rounded-xl' : ''}`}>
    <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
      <rect width="46" height="46" fill="url(#pattern0_73_129)"/>
      <defs>
        <pattern id="pattern0_73_129" patternContentUnits="objectBoundingBox" width="1" height="1">
          <use xlink:href="#image0_73_129" transform="scale(0.0111111)"/>
        </pattern>
        <image id="image0_73_129" width="90" height="90" preserveAspectRatio="none" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFoAAABaCAYAAAA4qEECAAAACXBIWXMAAAsTAAALEwEAmpwYAAADlElEQVR4nO2cO2gUQRjHN74iWkQRo4m9hyl9NYIIvkjEQhExWtjYCDEWvuITISkiWAgmnQgiiKiForG0EhSTKBZGiVGx8BmIgiKEqD8Z2YUD3Z297M7c7O73g0BSzLf/+d3H3NzcEM8TBEEQBEEQBEEQBEEoMAh/EdGWENGWENGWENGWENGWyKroceAw0JhizkbgiF87ddLKGTUBExwxmLfDRGBTecuDm6DBYN4GE4FN5S0PboJGg3kXmQhsKm95cBN0GMx71ERgU3nLg5tg3F9LG1PuZCVZ3gyzTFoNEdUpAiLaGtLRlhDRlhDRlhDRlhDRlhDRlhDRlhDRlhDRlhDRlhDRlhDRlhDR8fkG3PXPrDcDS4C5wAxguv/7YqAZOADcBL4Gg0V0NL+BO8A2YOYkjoinAi3AdREdLvgq0ORlBbLHC2C1lzXIFheB2V4WITtLxWkvy5ANyXsrmM8y4DjQB4yU7Sy++H/fBk4Cy82a/TeY6xyLMQe1hdsDPKuw9nP1IqrxRRd9A6jR5F8HDCd8zmu1vy6q6HdAnWYPfMZfWtJA1ekBphVN9FbNUqG63QR9RnY2uMmDiLxT/A8rJulLvbNxk5aIvKcsZTiXd9FvVNeGZF0B/LSUQ63Za/MsujMkZw3wqII6/f5JXrP/AqnDo4PA/QpqDKe2hOAeq0Jyboo5/i2wUTPnNcBQzHq78yj6uzo7Dsmpzpp1DADzYs67DrgXo+ZgHkUPhGScD0xoxr4H6iuc+5yYH3ZKeRN9JSTj9hhjd0xy/moN17E/b6J7QjKenexOJaaDp5r6lxNJ9h/iEt0hGW9pxvUmdNCpqd+fpH7wEJfoCsmo25K1J3SwS1P/VZL6wUOyIHpQM25nQgcbNPU/J6kfPCQLoh9rxrUmdLBeU380Sf3gIS7RFZJRRKfMCRFtjifAFmDWfzq5CTgEfNTUULuS7gQ/umPXH+orNWBpVpeOa0BtxJbrF+7Rq/t6zTXRIxGSW3GbtiyJbo/IVMlRZjUYypLolRGZdGtytZnIkuhSRKZRHEdEOyxaXeCuBqWidfTDKmUtFU30viplLRVNdK3/6cw2pUKJLvvHULZllwonuuw+W5u6jmXpDbJUdl+jpjCiXQIRLaIDvDyAdLSIDvDyANLRIjrAywNIR4voAC8PAC9xmzEvDwDncZtLXh4A6v0boi7yodJ72E4DLAQuAJ+qbdZnTHUysEAF/AMweIcEJ/TLogAAAABJRU5ErkJggg=="/>
      </defs>
    </svg>
  </button>

  <!-- Admin Logo (only visible if role is admin) -->
  {#if role === 'admin'}
    <button class={`text-white ${active === 'admin' ? 'bg-gray-700 p-2 rounded-xl' : ''}`}>
      <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <rect width="46" height="46" fill="url(#pattern0_73_38)"/>
        <defs>
          <pattern id="pattern0_73_38" patternContentUnits="objectBoundingBox" width="1" height="1">
            <use xlink:href="#image0_73_38" transform="scale(0.0111111)"/>
          </pattern>
          <image id="image0_73_38" width="90" height="90" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
        </defs>
      </svg>
    </button>
  {/if}
</div>
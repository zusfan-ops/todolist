<div x-data="{
        open: false,
        display: '0',
        stored: null,
        operator: null,
        waitingForOperand: false,
        init() {
            window.addEventListener('open-calculator', () => this.open = true);
        },
        resetIfError() {
            if (this.display === 'Error') this.clear();
        },
        inputDigit(d) {
            this.resetIfError();
            if (this.waitingForOperand) {
                this.display = d;
                this.waitingForOperand = false;
            } else {
                this.display = this.display === '0' ? d : this.display + d;
            }
        },
        inputDecimal() {
            this.resetIfError();
            if (this.waitingForOperand) {
                this.display = '0.';
                this.waitingForOperand = false;
                return;
            }
            if (!this.display.includes('.')) this.display += '.';
        },
        backspace() {
            this.resetIfError();
            this.display = this.display.length > 1 ? this.display.slice(0, -1) : '0';
        },
        clear() {
            this.display = '0';
            this.stored = null;
            this.operator = null;
            this.waitingForOperand = false;
        },
        calculate(a, b, op) {
            switch (op) {
                case '+': return a + b;
                case '-': return a - b;
                case '×': return a * b;
                case '÷': return b === 0 ? null : a / b;
                default: return b;
            }
        },
        clean(n) {
            return Math.round(n * 1e10) / 1e10;
        },
        performOperation(nextOperator) {
            this.resetIfError();
            const inputValue = parseFloat(this.display);

            if (this.stored === null) {
                this.stored = inputValue;
            } else if (this.operator) {
                const result = this.calculate(this.stored, inputValue, this.operator);
                if (result === null) { this.display = 'Error'; this.stored = null; this.operator = null; this.waitingForOperand = true; return; }
                this.stored = this.clean(result);
                this.display = String(this.stored);
            }

            this.waitingForOperand = true;
            this.operator = nextOperator;
        },
        equals() {
            this.resetIfError();
            if (this.operator === null || this.stored === null) return;
            const inputValue = parseFloat(this.display);
            const result = this.calculate(this.stored, inputValue, this.operator);
            this.display = result === null ? 'Error' : String(this.clean(result));
            this.stored = null;
            this.operator = null;
            this.waitingForOperand = true;
        }
     }"
     x-show="open" x-cloak @click.self="open = false"
     class="absolute inset-0 bg-ink-900/50 flex items-end z-30">
    <div class="bg-ink-900 w-full rounded-t-3xl p-5">
        <div class="w-10 h-1 bg-white/20 rounded-full mx-auto mb-4"></div>

        <div class="text-right mb-4 px-2">
            <p class="font-mono font-bold text-white text-4xl truncate" x-text="display"></p>
        </div>

        <div class="grid grid-cols-4 gap-2">
            <button @click="clear" class="bg-white/10 text-brick-500 font-disp font-bold py-4 rounded-xl">C</button>
            <button @click="backspace" class="bg-white/10 text-white font-disp font-bold py-4 rounded-xl">⌫</button>
            <button @click="performOperation('÷')" class="bg-white/10 text-vest-500 font-disp font-bold py-4 rounded-xl">÷</button>
            <button @click="performOperation('×')" class="bg-white/10 text-vest-500 font-disp font-bold py-4 rounded-xl">×</button>

            <button @click="inputDigit('7')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">7</button>
            <button @click="inputDigit('8')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">8</button>
            <button @click="inputDigit('9')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">9</button>
            <button @click="performOperation('-')" class="bg-white/10 text-vest-500 font-disp font-bold py-4 rounded-xl">−</button>

            <button @click="inputDigit('4')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">4</button>
            <button @click="inputDigit('5')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">5</button>
            <button @click="inputDigit('6')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">6</button>
            <button @click="performOperation('+')" class="bg-white/10 text-vest-500 font-disp font-bold py-4 rounded-xl">+</button>

            <button @click="inputDigit('1')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">1</button>
            <button @click="inputDigit('2')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">2</button>
            <button @click="inputDigit('3')" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">3</button>
            <button @click="equals" class="row-span-2 bg-vest-500 text-ink-900 font-disp font-bold py-4 rounded-xl">=</button>

            <button @click="inputDigit('0')" class="col-span-2 bg-white/5 text-white font-mono font-bold py-4 rounded-xl">0</button>
            <button @click="inputDecimal" class="bg-white/5 text-white font-mono font-bold py-4 rounded-xl">.</button>
        </div>
    </div>
</div>

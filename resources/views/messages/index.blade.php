@extends('layouts.app')

@section('title', 'Mensajes')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mensajes</h2>
            <p class="text-sm text-gray-600 mt-1">Mensajes internos por equipos.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('notifications.index') }}" class="btn-secondary">Notificaciones</a>
            <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card overflow-hidden" x-data="messageInbox({
        initialMessages: @js($messages ?? []),
        scopes: @js($messageScopes->where('type', 'team')->values() ?? []),
        fetchUrl: '{{ route('messages.data') }}',
        storeUrl: '{{ route('messages.store') }}',
        pollInterval: 1500,
    })" x-init="start()">
        <div class="card-body p-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-0" style="min-height: 560px; height: calc(100vh - 200px);">
                <div class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-gray-200 bg-slate-50/70 p-5 space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Conversaciones</h3>
                        <p class="text-xs text-gray-500 mt-1">Mensajes internos por equipos.</p>
                    </div>

                    <div class="space-y-4">
                        <template x-if="scopes.length === 0">
                            <p class="text-sm text-gray-500">No tienes equipos o proyectos asignados.</p>
                        </template>

                        <div class="space-y-2">
                            <template x-for="scope in scopes" :key="scope.key">
                                <button type="button"
                                        class="w-full text-left px-3 py-3 rounded-xl border border-transparent bg-white/70 hover:bg-white hover:border-gray-200 transition"
                                        :class="scope.key === scopeKey ? 'border-gray-200 shadow-sm' : ''"
                                        @click="selectScope(scope.key)">
                                    <p class="text-sm font-semibold text-gray-900" x-text="'Equipo: ' + scope.name"></p>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                                        <span>Rol: <span x-text="scope.role ?? '-'"></span></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Activo</span>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <template x-if="!canSend && scopeKey">
                            <p class="text-xs text-gray-500">Solo lectura: tu rol no puede enviar mensajes.</p>
                        </template>
                    </div>

                    <div class="rounded-xl bg-white border border-gray-200 p-3 text-xs text-gray-500">
                        <p class="font-semibold text-gray-700">Tip</p>
                        <p>Selecciona un equipo y luego un participante para enviar mensajes directos.</p>
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Participantes</p>
                        <button type="button"
                                class="w-full text-left px-3 py-2 rounded-lg border border-transparent hover:border-gray-200 hover:bg-white transition flex items-center gap-3"
                                :class="recipientId === '' ? 'bg-white border-gray-200' : ''"
                                @click="recipientId = ''">
                            <span class="h-9 w-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-semibold">ALL</span>
                            <div class="text-left">
                                <p class="text-sm font-medium text-gray-900">Todos</p>
                                <p class="text-xs text-gray-500">Enviar a todo el equipo</p>
                            </div>
                        </button>
                        <template x-if="recipients.length === 0">
                            <p class="text-xs text-gray-500">Sin miembros disponibles.</p>
                        </template>
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1" x-show="recipients.length > 0">
                            <template x-for="member in recipients" :key="member.id">
                                <button type="button" class="w-full text-left px-3 py-2 rounded-lg border border-transparent hover:border-gray-200 hover:bg-white transition flex items-center gap-3"
                                        :class="recipientId == member.id ? 'bg-white border-gray-200' : ''"
                                        @click="recipientId = member.id">
                                    <span class="h-9 w-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-semibold overflow-hidden">
                                        <img x-show="member.avatar" :src="member.avatar" class="h-9 w-9 rounded-full object-cover" alt="avatar">
                                        <span x-show="!member.avatar" x-text="member.initials"></span>
                                    </span>
                                    <div class="text-left">
                                        <p class="text-sm font-medium text-gray-900" x-text="member.name"></p>
                                        <p class="text-xs text-gray-500">Disponible</p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 flex flex-col">
                    <div class="border-b border-gray-200 px-5 py-4 flex items-center justify-between bg-white">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Chat</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="selectedScope ? (selectedScope.type === 'team' ? 'Equipo: ' : 'Proyecto: ') + selectedScope.name : 'Selecciona un destino'"></p>
                        </div>
                        <div class="text-xs text-gray-500" x-show="selectedScope">
                            <span x-text="canSend ? 'Puedes enviar mensajes' : 'Solo lectura'"></span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-white" x-ref="thread">
                        <template x-if="visibleMessages.length === 0">
                            <div class="text-sm text-gray-500">No hay mensajes en este chat.</div>
                        </template>
                        <template x-for="message in visibleMessages" :key="message.id">
                            <div class="flex" :class="message.is_own ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[78%] flex items-end gap-2" :class="message.is_own ? 'flex-row-reverse' : ''">
                                    <div class="h-9 w-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-semibold"
                                         :class="message.is_own ? 'bg-primary-600 text-white' : ''">
                                        <img x-show="!message.is_own && message.sender.avatar" :src="message.sender.avatar" class="h-9 w-9 rounded-full object-cover" alt="avatar">
                                        <span x-show="!message.sender.avatar || message.is_own" x-text="message.is_own ? 'Yo' : message.sender.initials"></span>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1" :class="message.is_own ? 'text-right' : ''">
                                            <span x-text="message.is_own ? 'Tú' : message.sender.name"></span>
                                            <span class="mx-1">·</span>
                                            <span x-text="message.created_label"></span>
                                        </div>
                                        <div class="rounded-2xl px-4 py-2 text-sm shadow-sm"
                                             :class="message.is_own ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-900 border border-gray-200'">
                                            <p class="whitespace-pre-line" x-text="message.body"></p>
                                        </div>
                                        <div class="text-[0.7rem] text-gray-400 mt-1 flex items-center gap-2" :class="message.is_own ? 'justify-end' : 'justify-start'">
                                            <span x-text="message.recipient ? 'Para: ' + message.recipient.name : 'Para: Todos'"></span>
                                            <template x-if="message.is_own && message.recipient">
                                                <span class="text-primary-600" x-text="message.read_by_recipient ? '✓✓' : '✓'"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <form class="border-t border-gray-200 px-5 py-4 space-y-3 bg-white" @submit.prevent="send()">
                        <div class="text-xs text-gray-500" x-show="selectedScope">
                            Enviando a: <span class="font-semibold text-gray-700" x-text="recipientId ? ((recipients.find(member => member.id == recipientId) || {}).name || 'Usuario') : 'Todos'"></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <textarea class="form-input w-full" rows="2" placeholder="Escribe un mensaje…" x-model="body"></textarea>
                            <button type="submit" class="btn-primary shrink-0" :disabled="!canSend || sending">
                                <span x-text="sending ? 'Enviando…' : 'Enviar'"></span>
                            </button>
                        </div>
                        <p class="text-xs text-red-600" x-text="error"></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function messageInbox({ initialMessages, scopes, fetchUrl, storeUrl, pollInterval }) {
            return {
                messages: Array.isArray(initialMessages) ? initialMessages : [],
                scopes: Array.isArray(scopes) ? scopes : [],
                scopeKey: '',
                recipientId: '',
                body: '',
                error: '',
                loading: false,
                sending: false,
                timer: null,
                get selectedScope() {
                    return this.scopes.find((scope) => scope.key === this.scopeKey);
                },
                get recipients() {
                    return this.selectedScope ? this.selectedScope.recipients : [];
                },
                get visibleMessages() {
                    if (!this.selectedScope) {
                        return this.messages.slice().reverse();
                    }
                    return this.messages
                        .filter((message) => message.scope && message.scope.type === this.selectedScope.type
                            && message.scope && message.scope.id === this.selectedScope.id)
                        .slice()
                        .reverse();
                },
                get canSend() {
                    return this.selectedScope ? this.selectedScope.can_send : false;
                },
                start() {
                    if (this.scopes.length > 0) {
                        this.scopeKey = this.scopes[0].key;
                    }
                    this.refresh();
                    this.timer = setInterval(() => this.refresh(), pollInterval || 5000);
                },
                selectScope(key) {
                    this.scopeKey = key;
                    this.recipientId = '';
                    this.error = '';
                    this.scrollToBottom();
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.thread) {
                            this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight;
                        }
                    });
                },
                syncRecipients() {
                    this.recipientId = '';
                    this.error = '';
                    this.scrollToBottom();
                },
                async refresh() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.selectedScope) {
                            params.set('scope_type', this.selectedScope.type);
                            params.set('scope_id', this.selectedScope.id);
                        }
                        const url = params.toString() ? `${fetchUrl}?${params.toString()}` : fetchUrl;

                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!response.ok) {
                            throw new Error('No se pudo actualizar.');
                        }
                        const data = await response.json();
                        this.messages = data.messages || [];
                        this.scrollToBottom();
                    } catch (error) {
                        this.error = 'No se pudo actualizar el inbox.';
                    } finally {
                        this.loading = false;
                    }
                },
                async send() {
                    if (!this.canSend) {
                        this.error = 'No tienes permisos para enviar mensajes.';
                        return;
                    }
                    if (!this.scopeKey) {
                        this.error = 'Selecciona un destino.';
                        return;
                    }
                    if (!this.body.trim()) {
                        this.error = 'Escribe un mensaje.';
                        return;
                    }
                    this.error = '';
                    this.sending = true;
                    const [scopeType, scopeId] = this.scopeKey.split(':');
                    const payload = {
                        scope_type: scopeType,
                        scope_id: scopeId,
                        recipient_id: this.recipientId || null,
                        body: this.body.trim(),
                    };

                    try {
                        const response = await fetch(storeUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify(payload),
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || 'No se pudo enviar.');
                        }

                        this.body = '';
                        await this.refresh();
                    } catch (error) {
                        this.error = error.message;
                    } finally {
                        this.sending = false;
                    }
                },
            }
        }
    </script>
@endsection

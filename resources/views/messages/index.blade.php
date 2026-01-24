@extends('layouts.app')

@section('title', 'Mensajes')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mensajes</h2>
            <p class="text-sm text-gray-600 mt-1">Bandeja social por equipos y proyectos.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('notifications.index') }}" class="btn-secondary">Notificaciones</a>
            <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card" x-data="messageInbox({
        initialMessages: @js($messages ?? []),
        scopes: @js($messageScopes ?? []),
        fetchUrl: '{{ route('messages.data') }}',
        storeUrl: '{{ route('messages.store') }}',
        pollInterval: 5000,
    })" x-init="start()">
        <div class="card-body space-y-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Social</h3>
                    <p class="text-xs text-gray-500 mt-1">Mensajes internos por equipos y proyectos.</p>
                </div>
                <button type="button" class="btn-secondary text-xs" @click="refresh()" :disabled="loading">
                    <span x-text="loading ? 'Actualizando...' : 'Actualizar'"></span>
                </button>
            </div>

            <div class="card border border-gray-200">
                <div class="card-body space-y-4">
                    <h4 class="text-sm font-semibold text-gray-800">Enviar mensaje</h4>
                    <template x-if="scopes.length === 0">
                        <p class="text-sm text-gray-500">No tienes equipos o proyectos asignados.</p>
                    </template>
                    <template x-if="scopes.length > 0">
                        <form class="space-y-4" @submit.prevent="send()">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="form-label">Destino</label>
                                    <select class="form-input w-full mt-2" x-model="scopeKey" @change="syncRecipients()">
                                        <option value="">Selecciona equipo o proyecto</option>
                                        <template x-for="scope in scopes" :key="scope.key">
                                            <option :value="scope.key" x-text="(scope.type === 'team' ? 'Equipo: ' : 'Proyecto: ') + scope.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Destinatario</label>
                                    <select class="form-input w-full mt-2" x-model="recipientId" :disabled="!selectedScope">
                                        <option value="">Todos</option>
                                        <template x-for="member in recipients" :key="member.id">
                                            <option :value="member.id" x-text="member.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Mensaje</label>
                                <textarea class="form-input w-full mt-2" rows="3" placeholder="Escribe un mensaje para el equipo o proyecto" x-model="body"></textarea>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs text-red-600" x-text="error"></p>
                                <button type="submit" class="btn-primary text-xs" :disabled="!canSend || sending">
                                    <span x-text="sending ? 'Enviando...' : 'Enviar mensaje'"></span>
                                </button>
                            </div>
                            <template x-if="!canSend && scopeKey">
                                <p class="text-xs text-gray-500">Solo lectura: tu rol no puede enviar mensajes.</p>
                            </template>
                        </form>
                    </template>
                </div>
            </div>

            <div class="space-y-3">
                <template x-if="messages.length === 0">
                    <p class="text-sm text-gray-500">Sin mensajes sociales.</p>
                </template>
                <template x-for="message in messages" :key="message.id">
                    <div class="border border-gray-200 rounded-lg p-4" :class="message.is_own ? 'bg-blue-50/60' : ''">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="message.sender.name"></p>
                                <p class="text-xs text-gray-500" x-text="message.created_label"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="message.scope.label + ': ' + message.scope.name"></p>
                                <p class="text-xs text-gray-500" x-text="message.recipient ? 'Para: ' + message.recipient.name : 'Para: Todos'"></p>
                            </div>
                            <span class="badge badge-info">Mensaje</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line" x-text="message.body"></p>
                    </div>
                </template>
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
                syncRecipients() {
                    this.recipientId = '';
                    this.error = '';
                },
                async refresh() {
                    this.loading = true;
                    try {
                        const response = await fetch(fetchUrl, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!response.ok) {
                            throw new Error('No se pudo actualizar.');
                        }
                        const data = await response.json();
                        this.messages = data.messages || [];
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

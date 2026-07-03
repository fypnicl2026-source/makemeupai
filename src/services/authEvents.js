let unauthorizedHandler = null;
let unverifiedHandler = null;

export function onUnauthorized(handler) {
  unauthorizedHandler = handler;
}

export function onUnverified(handler) {
  unverifiedHandler = handler;
}

export function triggerUnauthorized() {
  unauthorizedHandler?.();
}

export function triggerUnverified() {
  unverifiedHandler?.();
}

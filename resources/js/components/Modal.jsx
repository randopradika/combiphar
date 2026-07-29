import { useEffect } from 'react';

// `className` is appended to the box so a caller can restyle the panel itself
// (e.g. the dark recruitment-scam pop-up) without a second Modal component.
// Keep every prop in this signature — a prop used in the body but missing here
// throws a ReferenceError and blanks the page on open.
export default function Modal({ open, onClose, wide = false, flush = false, className = '', closeLabel = 'Close', children }) {
    useEffect(() => {
        if (!open) return;
        const onKey = (e) => { if (e.key === 'Escape') onClose(); };
        document.addEventListener('keydown', onKey);
        document.body.style.overflow = 'hidden';
        return () => {
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
        };
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="modal open" role="dialog" aria-modal="true">
            <div className="modal__backdrop" onClick={onClose}></div>
            <div className={'modal__box' + (wide ? ' modal__box--wide' : '') + (flush ? ' modal__box--flush' : '') + (className ? ' ' + className : '')}>
                <button className="modal__close" onClick={onClose} aria-label={closeLabel}>
                    {closeLabel} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
                {children}
            </div>
        </div>
    );
}
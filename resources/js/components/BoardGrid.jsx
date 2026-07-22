import { usePage } from "@inertiajs/react"

// Shared board-member grid (dark purple cards, role↔Biography hover swap).
// Used by the About page (Commissioners/Directors/Audit Committee/Corporate
// Secretary) and the CSR Komite Audit "board" layout so both render identically.
// `reveal` toggles the .rv scroll-reveal (opacity:0 until observed). Keep it on
// for page-load contexts (About, standalone detail); turn it off when the grid is
// swapped in by a client-side tab switch (CSR sub-menu), where the reveal observer
// doesn't re-run and the cards would otherwise stay invisible.
export default function BoardGrid({ people, onOpen, reveal = true }) {
  const {
    props: { locale },
  } = usePage()
  return (
    <div className="board-grid" style={{ marginTop: 44 }}>
      {people.map((p, i) => (
        <div
          className={"board-card" + (reveal ? " rv" : "")}
          key={i}
          onClick={() => onOpen(p)}
        >
          <div
            className="board-card__photo"
            style={p.photo ? { backgroundImage: `url('${p.photo}')` } : {}}
          ></div>
          <div className="board-card__info">
            <h4>{p.name}</h4>
            <div className="board-card__swap">
              <span className="board-card__role">{p.role}</span>
              <span className="board-card__bio-btn">
                {locale === "en" ? "Biography" : "Biografi"}
              </span>
            </div>
          </div>
        </div>
      ))}
    </div>
  )
}

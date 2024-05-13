<?php
trait HtmlUtils {
	public function escapeHtml( $content ) {
		return htmlspecialchars( $content, ENT_QUOTES, 'UTF-8' );
	}
}

abstract class BaseSplideArrow {
	use HtmlUtils;

	protected $layoutClass    = '';
	protected $additionalText = '';
	protected $customClasses  = '';
	protected $buttonId       = 'default-btn-id';

	public function SwipeText( string $text = '' ): self {
		$this->additionalText = $text;
		return $this;
	}

	public function ButtonId( string $buttonId ): self {
		$this->buttonId = $buttonId;
		return $this;
	}

	public function Render( string $customClasses = '' ): void {
		$this->customClasses = $customClasses;
		echo $this->generateHtml();
	}

	abstract protected function generateHtml(): string;

	protected function generateButtons(): string {
		$prevButtonId = $this->buttonId . '--prev';
		$nextButtonId = $this->buttonId . '--next';

		return sprintf(
			'<button id="%s" class="btn--swipe" aria-label="Poprzedni slajd"><svg class="c-white" width="16" height="16" viewBox="0 0 16 16"><use xlink:href="#tiny-left-arrow"></use></svg></button><button id="%s" class="btn--swipe" aria-label="Następny slajd"><svg class="c-white" width="16" height="16" viewBox="0 0 16 16"><use xlink:href="#tiny-right-arrow"></use></svg></button>',
			$this->escapeHtml( $prevButtonId ),
			$this->escapeHtml( $nextButtonId )
		);
	}

	protected function generateAdditionalText(): string {
		if ( $this->additionalText ) {
			return '<p class="m-0 p-0 c-heading">' . $this->escapeHtml( $this->additionalText ) . '</p>';
		}
		return '';
	}
}

class FloatableSplideArrow extends BaseSplideArrow {
	protected function generateHtml(): string {
		return sprintf(
			'<div class="splide__arrows %s" data-layout="floatable"><div class="splide__btn">%s</div></div>',
			$this->escapeHtml( $this->customClasses ),
			$this->generateButtons()
		);
	}
}

class LayoutSplideArrow extends BaseSplideArrow {
	private $layout = false;

	public function Layout( bool $layout = true ): self {
		$this->layoutClass = $layout ? 'd-flex aic' : 'd-grid';
		return $this;
	}

	protected function generateHtml(): string {
		return sprintf(
			'<div class="splide__arrows %s"><div class="%s" style="gap:24px">%s<div class="splide__btn d-flex">%s</div></div></div>',
			$this->escapeHtml( $this->customClasses ),
			$this->layoutClass,
			$this->generateAdditionalText(),
			$this->generateButtons()
		);
	}
}

class NormalSplideArrow extends BaseSplideArrow {
	protected function generateHtml(): string {
		return sprintf(
			'<div class="splide__arrows %s">%s<div class="splide__btn d-flex">%s</div></div>',
			$this->escapeHtml( $this->customClasses ),
			$this->generateAdditionalText(),
			$this->generateButtons()
		);
	}
}

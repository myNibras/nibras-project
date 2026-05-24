import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CancellationAndRefundPolicyComponent } from './cancellation-and-refund-policy.component';

describe('CancellationAndRefundPolicyComponent', () => {
  let component: CancellationAndRefundPolicyComponent;
  let fixture: ComponentFixture<CancellationAndRefundPolicyComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CancellationAndRefundPolicyComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CancellationAndRefundPolicyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
